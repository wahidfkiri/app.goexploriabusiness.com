<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        $forceHttps = (bool) config('app.force_https', false);
        $targetHost = $this->resolveTargetHost($request);

        if (! $forceHttps && $targetHost === $request->getHost()) {
            return $next($request);
        }

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        if ($forceHttps && ! $this->requestUsesHttps($request)) {
            return $this->redirectToCanonical($request, $targetHost, true);
        }

        if ($targetHost !== $request->getHost()) {
            return $this->redirectToCanonical($request, $targetHost, $this->requestUsesHttps($request));
        }

        $response = $next($request);
        if ($forceHttps && $this->requestUsesHttps($request)) {
            $this->applyHsts($response);
        }

        return $response;
    }

    protected function requestUsesHttps(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }

        $forwardedProto = strtolower((string) $request->headers->get('x-forwarded-proto', ''));
        if (in_array($forwardedProto, ['https', 'wss'], true)) {
            return true;
        }

        $forwardedSsl = strtolower((string) $request->headers->get('x-forwarded-ssl', ''));
        if ($forwardedSsl === 'on') {
            return true;
        }

        $frontEndHttps = strtolower((string) $request->headers->get('front-end-https', ''));
        if ($frontEndHttps === 'on') {
            return true;
        }

        $cfVisitor = strtolower((string) $request->headers->get('cf-visitor', ''));
        if (str_contains($cfVisitor, 'https')) {
            return true;
        }

        return false;
    }

    protected function redirectToCanonical(Request $request, string $host, bool $secure): RedirectResponse
    {
        $uri = $request->getRequestUri();
        $scheme = $secure ? 'https' : 'http';
        $targetUrl = $scheme . '://' . $host . $uri;
        $status = in_array($request->getMethod(), ['GET', 'HEAD'], true) ? 301 : 307;

        return redirect()->to($targetUrl, $status);
    }

    protected function resolveTargetHost(Request $request): string
    {
        $canonicalHost = trim((string) config('app.canonical_host', ''));
        if ($canonicalHost !== '') {
            return $canonicalHost;
        }

        $host = $request->getHost();
        if ((bool) config('app.strip_www', false) && str_starts_with(strtolower($host), 'www.')) {
            return substr($host, 4);
        }

        return $host;
    }

    protected function applyHsts(Response $response): void
    {
        $seconds = max(0, (int) config('app.https_hsts_seconds', 31536000));

        if ($seconds > 0) {
            $response->headers->set('Strict-Transport-Security', 'max-age=' . $seconds . '; includeSubDomains; preload');
        }
    }
}
