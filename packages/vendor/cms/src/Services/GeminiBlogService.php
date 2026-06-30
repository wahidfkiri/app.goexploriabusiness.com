<?php

namespace Vendor\Cms\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiBlogService
{
    private string $basePath = 'https://generativelanguage.googleapis.com';

    public function generateArticleAndSeo(array $payload): array
    {
        $apiKey = (string) config('gemini.api_key');
        $requestedModel = (string) config('gemini.model', 'gemini-1.5-flash-latest');
        $usedModel = 'local-template';
        $usedApiVersion = 'n/a';
        $aiError = '';

        if (blank($apiKey)) {
            $data = $this->buildLocalTemplateFromPayload($payload);

            return array_merge($data, [
                'status' => 'draft',
                'ai_model_used' => $usedModel,
                'ai_api_version' => $usedApiVersion,
                'ai_error' => 'GEMINI_API_KEY manquante: fallback local utilise.',
            ]);
        }

        $prompt = $this->buildPrompt($payload);
        [$response, $geminiModel, $geminiApiVersion, $errors] = $this->callGenerateContentWithFallback($apiKey, $requestedModel, $prompt);
        $text = '';
        $data = null;

        if ($response) {
            $usedModel = (string) $geminiModel;
            $usedApiVersion = (string) $geminiApiVersion;
            $text = (string) data_get($response, 'candidates.0.content.parts.0.text', '');
            $data = $this->decodeJsonPayload($text);

            if (!is_array($data) && $text !== '') {
                $repaired = $this->repairJsonWithGemini($apiKey, $usedModel, $usedApiVersion, $text);
                $data = is_array($repaired) ? $repaired : null;
            }
        } else {
            $aiError = 'Generation Gemini indisponible: ' . implode(' | ', $errors);
        }

        if (!is_array($data) && $text !== '') {
            $data = $this->buildSafeFallbackFromText($payload, $text);
        }
        if (!is_array($data)) {
            $data = $this->buildLocalTemplateFromPayload($payload);
            if ($aiError === '') {
                $aiError = 'Reponse Gemini non exploitable: fallback local utilise.';
            }
            $usedModel = 'local-template';
            $usedApiVersion = 'n/a';
        }

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'excerpt' => trim((string) ($data['excerpt'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'seo_title' => trim((string) ($data['seo_title'] ?? '')),
            'seo_description' => trim((string) ($data['seo_description'] ?? '')),
            'seo_keywords' => trim((string) ($data['seo_keywords'] ?? '')),
            'tags' => trim((string) ($data['tags'] ?? '')),
            'canonical_url' => trim((string) ($data['canonical_url'] ?? '')),
            'status' => 'draft',
            'ai_model_used' => $usedModel,
            'ai_api_version' => $usedApiVersion,
            'ai_error' => $aiError,
        ];
    }

    private function callGenerateContentWithFallback(string $apiKey, string $requestedModel, string $prompt): array
    {
        $versions = ['v1beta', 'v1'];
        $models = $this->discoverModelCandidates($apiKey, $requestedModel);
        $errors = [];

        foreach ($versions as $version) {
            foreach ($models as $model) {
                $url = sprintf('%s/%s/models/%s:generateContent?key=%s', $this->basePath, $version, $model, $apiKey);

                $response = Http::timeout(90)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'topP' => 0.9,
                        'topK' => 40,
                        'maxOutputTokens' => 4096,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

                if ($response->successful()) {
                    return [$response->json(), $model, $version, $errors];
                }

                $status = $response->status();
                $message = (string) data_get($response->json(), 'error.message', $response->body());
                $errors[] = sprintf('%s/%s -> HTTP %s: %s', $version, $model, $status, $message);
            }
        }

        return [null, null, null, $errors];
    }

    private function discoverModelCandidates(string $apiKey, string $requestedModel): array
    {
        $discovered = [];

        try {
            $url = sprintf('%s/%s/models?key=%s', $this->basePath, 'v1beta', $apiKey);
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $models = (array) data_get($response->json(), 'models', []);

                foreach ($models as $model) {
                    $name = (string) data_get($model, 'name', '');
                    $methods = (array) data_get($model, 'supportedGenerationMethods', []);

                    if (!in_array('generateContent', $methods, true)) {
                        continue;
                    }

                    $name = preg_replace('/^models\//', '', trim($name));
                    if ($name !== '') {
                        $discovered[] = $name;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silence here: fallback list below still applies.
        }

        $priority = $this->buildModelCandidates($requestedModel);
        $all = array_values(array_unique(array_merge($priority, $discovered)));

        return $all;
    }

    private function buildModelCandidates(string $requestedModel): array
    {
        $requestedModel = trim($requestedModel);
        $requestedModel = preg_replace('/^models\//', '', $requestedModel);

        $candidates = [
            $requestedModel,
            'gemini-2.5-flash',
            'gemini-2.5-pro',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-flash-latest',
            'gemini-flash-lite-latest',
            'gemini-pro-latest',
        ];

        $normalized = [];
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && !in_array($candidate, $normalized, true)) {
                $normalized[] = $candidate;
            }
        }

        return $normalized;
    }

    private function buildPrompt(array $payload): string
    {
        $subject = (string) ($payload['subject'] ?? '');
        $businessName = (string) ($payload['business_name'] ?? 'Entreprise');
        $businessContext = (string) ($payload['business_context'] ?? '');
        $targetKeyword = (string) ($payload['target_keyword'] ?? '');
        $tone = (string) ($payload['tone'] ?? 'professionnel');
        $lang = (string) ($payload['language'] ?? 'fr');
        $minWords = (int) ($payload['min_words'] ?? 900);

        return <<<PROMPT
Tu es un redacteur SEO expert.

Contexte entreprise: {$businessName}
Contexte metier: {$businessContext}
Sujet article: {$subject}
Mot-cle principal: {$targetKeyword}
Ton: {$tone}
Langue: {$lang}
Longueur minimum: {$minWords} mots

Objectifs:
1) Rediger un article blog complet en HTML (h2, h3, paragraphes, listes).
2) Optimiser le SEO on-page.
3) Generer des champs prets a sauvegarder dans un CMS.

Reponds UNIQUEMENT en JSON valide, sans markdown, sans balises ```json.
Format JSON exact:
{
  "title": "Titre SEO et humain",
  "slug": "slug-seo",
  "excerpt": "Resume 2-3 phrases",
  "content": "<h2>...</h2><p>...</p>",
  "seo_title": "meta title <= 60 caracteres",
  "seo_description": "meta description <= 160 caracteres",
  "seo_keywords": "mot-cle principal, variation 1, variation 2",
  "tags": "tag1, tag2, tag3",
  "canonical_url": ""
}
PROMPT;
    }

    private function extractJson(string $text): string
    {
        if (preg_match('/```json\s*(\{.*\})\s*```/is', $text, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/(\{.*\})/is', $text, $matches)) {
            return trim($matches[1]);
        }

        return trim($text);
    }

    private function decodeJsonPayload(string $text): ?array
    {
        $json = $this->extractJson($text);
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded)) {
            $decodedFromString = json_decode($decoded, true);
            if (is_array($decodedFromString)) {
                return $decodedFromString;
            }
        }

        $normalized = trim($json);
        $normalized = str_replace(["\r\n", "\r"], "\n", $normalized);
        $normalized = preg_replace('/,\s*([}\]])/', '$1', $normalized);
        $normalized = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $normalized);

        $decoded = json_decode($normalized, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    private function repairJsonWithGemini(string $apiKey, string $model, string $apiVersion, string $rawText): ?array
    {
        try {
            $url = sprintf('%s/%s/models/%s:generateContent?key=%s', $this->basePath, $apiVersion, $model, $apiKey);
            $prompt = <<<PROMPT
Transforme le contenu suivant en JSON strict VALIDE.
Ne renvoie que du JSON, sans markdown, sans texte additionnel.

Schema JSON attendu:
{
  "title": "string",
  "slug": "string",
  "excerpt": "string",
  "content": "string HTML",
  "seo_title": "string",
  "seo_description": "string",
  "seo_keywords": "string",
  "tags": "string",
  "canonical_url": "string"
}

Contenu a transformer:
{$rawText}
PROMPT;

            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 2048,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if (!$response->successful()) {
                return null;
            }

            $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            if (blank($text)) {
                return null;
            }

            return $this->decodeJsonPayload($text);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildSafeFallbackFromText(array $payload, string $rawText): array
    {
        $subject = trim((string) ($payload['subject'] ?? 'Article'));
        $title = $subject !== '' ? $subject : 'Article';

        $decoded = $this->decodeJsonPayload($rawText);
        if (is_array($decoded)) {
            $title = trim((string) ($decoded['title'] ?? $title)) ?: $title;
            $content = trim((string) ($decoded['content'] ?? ''));
            $excerpt = trim((string) ($decoded['excerpt'] ?? ''));

            if ($content === '') {
                $content = '<p>Contenu genere automatiquement. Merci de le completer.</p>';
            }
            if ($excerpt === '') {
                $excerpt = $this->makeExcerptFromHtml($content, 220);
            }

            return [
                'title' => $title,
                'slug' => $this->slugify((string) ($decoded['slug'] ?? $title)),
                'excerpt' => $excerpt,
                'content' => $content,
                'seo_title' => trim((string) ($decoded['seo_title'] ?? mb_substr($title, 0, 60))),
                'seo_description' => trim((string) ($decoded['seo_description'] ?? mb_substr($excerpt, 0, 160))),
                'seo_keywords' => trim((string) ($decoded['seo_keywords'] ?? trim((string) ($payload['target_keyword'] ?? '')))),
                'tags' => trim((string) ($decoded['tags'] ?? '')),
                'canonical_url' => trim((string) ($decoded['canonical_url'] ?? '')),
            ];
        }

        $jsonLikeTitle = $this->extractFieldFromJsonLike($rawText, 'title');
        $jsonLikeExcerpt = $this->extractFieldFromJsonLike($rawText, 'excerpt');
        $jsonLikeContent = $this->extractFieldFromJsonLike($rawText, 'content');

        if ($jsonLikeTitle !== null && trim($jsonLikeTitle) !== '') {
            $title = trim($jsonLikeTitle);
        }

        $content = trim((string) $jsonLikeContent);
        if ($content === '') {
            $plain = $this->cleanPlainText($rawText);
            $content = $plain !== '' ? '<p>' . e($plain) . '</p>' : '<p>Contenu genere automatiquement. Merci de le completer.</p>';
        }

        $excerpt = trim((string) $jsonLikeExcerpt);
        if ($excerpt === '') {
            $excerpt = $this->makeExcerptFromHtml($content, 220);
        }

        return [
            'title' => $title,
            'slug' => $this->slugify($title),
            'excerpt' => $excerpt,
            'content' => $content,
            'seo_title' => mb_substr($title, 0, 60),
            'seo_description' => mb_substr($excerpt, 0, 160),
            'seo_keywords' => trim((string) ($payload['target_keyword'] ?? '')),
            'tags' => '',
            'canonical_url' => '',
        ];
    }

    private function extractFieldFromJsonLike(string $text, string $field): ?string
    {
        $pattern = sprintf('/"%s"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/s', preg_quote($field, '/'));
        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }

        return stripcslashes((string) $matches[1]);
    }

    private function cleanPlainText(string $text): string
    {
        $cleaned = trim($text);
        $cleaned = preg_replace('/^\s*```json/i', '', $cleaned);
        $cleaned = preg_replace('/```\s*$/', '', (string) $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', strip_tags((string) $cleaned));
        $cleaned = trim((string) $cleaned, " \t\n\r\0\x0B{}");

        return trim((string) $cleaned);
    }

    private function makeExcerptFromHtml(string $html, int $limit = 220): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
        if ($text === '') {
            return '';
        }

        return Str::limit($text, $limit, '');
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'article';
        }

        $slug = Str::slug(Str::ascii($value));

        return $slug !== '' ? $slug : 'article';
    }

    private function buildLocalTemplateFromPayload(array $payload): array
    {
        $subject = trim((string) ($payload['subject'] ?? 'Article professionnel'));
        $businessName = trim((string) ($payload['business_name'] ?? 'Votre entreprise'));
        $context = trim((string) ($payload['business_context'] ?? ''));
        $keyword = trim((string) ($payload['target_keyword'] ?? ''));
        $tone = trim((string) ($payload['tone'] ?? 'professionnel'));

        $title = $subject !== '' ? $subject : 'Article professionnel';
        $slug = $this->slugify($title);

        $safeBusinessName = e($businessName);
        $safeTitle = e($title);
        $introContext = $context !== '' ? e($context) : 'un contexte metier specifique';
        $keywordLine = $keyword !== '' ? '<p><strong>Mot-cle principal:</strong> ' . e($keyword) . '</p>' : '';

        $content = <<<HTML
<h2>Introduction</h2>
<p>Ce brouillon a ete genere automatiquement pour {$safeBusinessName} avec un ton {$tone}.</p>
<p>Le sujet traite est: <strong>{$safeTitle}</strong>.</p>
{$keywordLine}
<h2>Contexte</h2>
<p>{$introContext}</p>
<h2>Plan recommande</h2>
<ul>
  <li>Definir les objectifs et la cible.</li>
  <li>Presenter les services/produits et la proposition de valeur.</li>
  <li>Ajouter des preuves (cas clients, avis, resultats).</li>
  <li>Conclure avec un appel a l'action clair.</li>
</ul>
<h2>Conclusion</h2>
<p>Ce contenu est un brouillon de secours. Vous pouvez l'enrichir puis publier.</p>
HTML;

        $excerpt = 'Brouillon automatique pour "' . $title . '" a finaliser avant publication.';

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'seo_title' => Str::limit($title, 60, ''),
            'seo_description' => Str::limit($excerpt, 160, ''),
            'seo_keywords' => $keyword,
            'tags' => '',
            'canonical_url' => '',
        ];
    }
}
