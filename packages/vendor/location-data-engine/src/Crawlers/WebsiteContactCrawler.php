<?php

namespace Vendor\LocationDataEngine\Crawlers;

use DOMDocument;
use Illuminate\Support\Str;

class WebsiteContactCrawler
{
    public function crawl(string $url, array $contactPaths = []): array
    {
        $html = $this->download($url);
        $emails = $this->extractEmails($html);
        $socialLinks = $this->extractSocialLinks($html);
        $bookingLinks = $this->extractBookingLinks($html);
        $contactPage = $this->findContactPage($url, $html, $contactPaths);

        if ($contactPage) {
            $contactHtml = $this->download($contactPage);
            $emails = array_merge($emails, $this->extractEmails($contactHtml));
            $socialLinks = array_merge_recursive($socialLinks, $this->extractSocialLinks($contactHtml));
            $bookingLinks = array_merge($bookingLinks, $this->extractBookingLinks($contactHtml));
        }

        return [
            'emails' => array_values(array_unique($emails)),
            'social_links' => array_map(fn ($links) => array_values(array_unique($links)), $socialLinks),
            'booking_links' => array_values(array_unique($bookingLinks)),
            'contact_page' => $contactPage,
            'raw' => ['url' => $url],
        ];
    }

    protected function download(string $url): string
    {
        return (string) @file_get_contents($url);
    }

    protected function extractEmails(string $html): array
    {
        preg_match_all('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $html, $matches);

        return $matches[0] ?? [];
    }

    protected function extractSocialLinks(string $html): array
    {
        $map = [
            'facebook' => [],
            'instagram' => [],
            'linkedin' => [],
            'youtube' => [],
            'tiktok' => [],
            'whatsapp' => [],
        ];

        preg_match_all("/https?:\\/\\/[^\"'\\s<>]+/i", $html, $matches);

        foreach ($matches[0] ?? [] as $url) {
            foreach (array_keys($map) as $platform) {
                if (Str::contains(Str::lower($url), $platform)) {
                    $map[$platform][] = $url;
                }
            }
        }

        return $map;
    }

    protected function extractBookingLinks(string $html): array
    {
        $bookingDomains = ['booking.com', 'airbnb', 'expedia', 'tripadvisor'];
        preg_match_all("/https?:\\/\\/[^\"'\\s<>]+/i", $html, $matches);

        return array_values(array_filter($matches[0] ?? [], function (string $url) use ($bookingDomains) {
            foreach ($bookingDomains as $domain) {
                if (str_contains(strtolower($url), $domain)) {
                    return true;
                }
            }

            return false;
        }));
    }

    protected function findContactPage(string $origin, string $html, array $contactPaths): ?string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html ?: '<html></html>');
        $links = $dom->getElementsByTagName('a');

        foreach ($links as $link) {
            $href = (string) $link->getAttribute('href');
            foreach ($contactPaths as $path) {
                if (Str::contains(Str::lower($href), Str::lower($path))) {
                    if (Str::startsWith($href, 'http')) {
                        return $href;
                    }

                    return rtrim($origin, '/') . '/' . ltrim($href, '/');
                }
            }
        }

        return null;
    }
}
