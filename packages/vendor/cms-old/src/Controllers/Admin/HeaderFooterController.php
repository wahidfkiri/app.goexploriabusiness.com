<?php

namespace Vendor\Cms\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Vendor\Cms\Models\HeaderFooter;

class HeaderFooterController extends Controller
{
    public function edit(Request $request, $etablissementId, string $type)
    {
        $etablissement = Etablissement::findOrFail($etablissementId);
        $type = $this->normalizeType($type);
        $item = $this->firstOrCreateItem($etablissement, $type, $request);

        return view('cms::admin.header-footer.edit-content', compact('etablissement', 'item', 'type'));
    }

    public function load(Request $request, $etablissementId, string $type): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $type = $this->normalizeType($type);
            $item = $this->firstOrCreateItem($etablissement, $type, $request);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'type' => $item->type,
                    'name' => $item->name,
                    'content' => $this->cleanEditorContent($item->content ?? ''),
                    'html_content' => $this->cleanEditorContent($item->html_content ?? ''),
                    'css_content' => $item->css_content ?? '',
                    'settings' => $item->settings ?? [],
                    'updated_at' => optional($item->updated_at)->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Header/footer load error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function save(Request $request, $etablissementId, string $type): JsonResponse
    {
        try {
            $etablissement = Etablissement::findOrFail($etablissementId);
            $type = $this->normalizeType($type);
            $item = $this->firstOrCreateItem($etablissement, $type, $request);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'html_content' => 'nullable|string',
                'css_content' => 'nullable|string',
                'settings' => 'nullable|array',
            ]);

            $html = $this->cleanEditorContent($validated['html_content'] ?? '');
            $css = (string) ($validated['css_content'] ?? '');
            $content = $this->cleanEditorContent($validated['content'] ?? '');

            if ($content === '') {
                $content = trim($css) !== '' ? '<style>' . $css . '</style>' . $html : $html;
            }

            $item->update([
                'name' => $validated['name'] ?? $this->defaultName($type),
                'content' => $content,
                'html_content' => $html,
                'css_content' => $css,
                'settings' => $validated['settings'] ?? $item->settings,
                'updated_by' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($this->label($type)) . ' sauvegardé avec succès',
                'data' => [
                    'id' => $item->id,
                    'type' => $item->type,
                    'updated_at' => optional($item->fresh()->updated_at)->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Header/footer save error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeType(string $type): string
    {
        abort_unless(in_array($type, [HeaderFooter::TYPE_HEADER, HeaderFooter::TYPE_FOOTER], true), 404);

        return $type;
    }

    private function firstOrCreateItem(Etablissement $etablissement, string $type, Request $request): HeaderFooter
    {
        return HeaderFooter::firstOrCreate(
            [
                'etablissement_id' => $etablissement->id,
                'type' => $type,
            ],
            [
                'name' => $this->defaultName($type),
                'content' => $this->defaultContent($type, $etablissement),
                'html_content' => $this->defaultHtml($type, $etablissement),
                'css_content' => $this->defaultCss($type),
                'settings' => [],
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]
        );
    }

    private function defaultName(string $type): string
    {
        return $type === HeaderFooter::TYPE_HEADER ? 'Header du site' : 'Footer du site';
    }

    private function cleanEditorContent(?string $content): string
    {
        $cleaned = preg_replace('/^(\s*(?:<style[\s\S]*?<\/style>\s*)*)\d+\s*(?=<)/i', '$1', (string) $content);

        return preg_replace('/^\s*\d+\s*(?=<)/', '', (string) $cleaned) ?? '';
    }

    private function label(string $type): string
    {
        return $type === HeaderFooter::TYPE_HEADER ? 'header' : 'footer';
    }

    private function defaultContent(string $type, Etablissement $etablissement): string
    {
        $html = $this->defaultHtml($type, $etablissement);
        $css = $this->defaultCss($type);

        return '<style>' . $css . '</style>' . $html;
    }

    private function defaultHtml(string $type, Etablissement $etablissement): string
    {
        $name = e($etablissement->name ?? 'Entreprise');

        if ($type === HeaderFooter::TYPE_HEADER) {
            return <<<HTML
<header class="cms-custom-header">
    <div class="cms-custom-header__brand">{$name}</div>
    <nav class="cms-custom-header__nav">
        <a href="#accueil">Accueil</a>
        <a href="#services">Services</a>
        <a href="#contact">Contact</a>
    </nav>
</header>
HTML;
        }

        return <<<HTML
<footer class="cms-custom-footer">
    <div>
        <strong>{$name}</strong>
        <p>Merci de votre visite.</p>
    </div>
    <div class="cms-custom-footer__links">
        <a href="#contact">Contact</a>
        <a href="#mentions">Mentions légales</a>
    </div>
</footer>
HTML;
    }

    private function defaultCss(string $type): string
    {
        if ($type === HeaderFooter::TYPE_HEADER) {
            return <<<CSS
.cms-custom-header {
    align-items: center;
    background: #0f172a;
    color: #fff;
    display: flex;
    justify-content: space-between;
    padding: 18px 32px;
}
.cms-custom-header__brand {
    font-size: 20px;
    font-weight: 800;
}
.cms-custom-header__nav {
    display: flex;
    gap: 18px;
}
.cms-custom-header__nav a {
    color: #e2e8f0;
    text-decoration: none;
}
CSS;
        }

        return <<<CSS
.cms-custom-footer {
    align-items: center;
    background: #111827;
    color: #f8fafc;
    display: flex;
    justify-content: space-between;
    padding: 32px;
}
.cms-custom-footer p {
    margin: 6px 0 0;
    color: #cbd5e1;
}
.cms-custom-footer__links {
    display: flex;
    gap: 16px;
}
.cms-custom-footer__links a {
    color: #bfdbfe;
    text-decoration: none;
}
CSS;
    }
}
