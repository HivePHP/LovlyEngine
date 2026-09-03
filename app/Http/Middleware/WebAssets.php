<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use HivePHP\Http\MiddlewareInterface;
use HivePHP\Http\Request;
use HivePHP\Http\Response;

/**
 * Marks a request as a "web" (HTML) request.
 *
 * Static assets are resolved per-page through HivePHP\Assets\Assets::usePage()
 * — each controller declares only the bundles it needs (see config/assets.php).
 *
 * For SPA (AJAX) navigation requests this middleware captures the rendered
 * document and returns a compact JSON envelope so the client can swap just the
 * content fragment — without a full page reload — while keeping full server-side
 * auth/CSRF/grammar intact.
 */
final class WebAssets implements MiddlewareInterface
{
    private const CONTENT_START = '<!-- HIVE-CONTENT:START -->';
    private const CONTENT_END   = '<!-- HIVE-CONTENT:END -->';
    private const CSS_START     = '<!-- HIVE-CSS:START -->';
    private const CSS_END       = '<!-- HIVE-CSS:END -->';
    private const JS_START      = '<!-- HIVE-JS:START -->';
    private const JS_END        = '<!-- HIVE-JS:END -->';

    public function __construct(
        private readonly Response $response
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        if (!$this->wantsSpaFragment($request)) {
            $next($request);
            return;
        }

        // The inner pipeline echoes the full document through Response::html().
        // We buffer it so it can be mined for the content fragment below.
        //
        // Caveat: this assumes everything produced inside $next() is the HTML
        // document. If a route triggers Response::redirect(), PHP's exit()
        // aborts here and the 302 (Location) goes to the client directly —
        // which is exactly what the SPA client wants (redirect: 'manual' ->
        // full page load). A fully Response-object pipeline would remove the
        // need for output buffering entirely; that is a larger, separate
        // refactor of Router/dispatch.
        ob_start();

        try {
            $next($request);
            $html = ob_get_clean();
        } catch (\Throwable $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        if ($html === false) {
            return;
        }

        // If the inner pipeline already produced a JSON response (e.g. an error
        // thrown before rendering), relay it as-is instead of a fragment.
        if ($this->responseIsJson()) {
            $this->response->json(
                json_decode($html, true),
                http_response_code() ?: 200
            );
            return;
        }

        $envelope = $this->buildEnvelope($html, $this->requestUrl($request));

        if ($envelope === null) {
            // Could not reliably extract the fragment — tell the client to
            // perform a normal (full) page navigation instead.
            $envelope = ['status' => 'reload'];
        }

        $this->response->json($envelope);
    }

    private function wantsSpaFragment(Request $request): bool
    {
        // The dedicated SPA header is the single source of truth. X-Requested-With
        // is skipped on purpose: a plain AJAX (e.g. POST /status) must NOT be
        // turned into a fragment navigation.
        return $request->header('X-Hive-Spa') === '1';
    }

    private function requestUrl(Request $request): string
    {
        $path  = $request->getPath();
        $query = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);

        return $query !== null ? $path . '?' . $query : $path;
    }

    /**
     * Whether the inner pipeline emitted a JSON body, determined by the
     * Content-Type header it set (reliable) with a json_decode fallback.
     */
    private function responseIsJson(): bool
    {
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') === 0
                && stripos($header, 'application/json') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function buildEnvelope(string $html, string $url): ?array
    {
        $content = $this->between($html, self::CONTENT_START, self::CONTENT_END);

        if ($content === null) {
            return null;
        }

        $title = '';
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
        }

        $cssBlock = $this->between($html, self::CSS_START, self::CSS_END) ?? '';
        $jsBlock  = $this->between($html, self::JS_START, self::JS_END) ?? '';

        // Accept single or double quotes and optional query strings (e.g. ?v=1).
        preg_match_all('#href=["\']([^"\']+\.css(?:\?[^"\']*)?)["\']#i', $cssBlock, $cssMatches);
        preg_match_all('#src=["\']([^"\']+\.js(?:\?[^"\']*)?)["\']#i', $jsBlock, $jsMatches);

        $css = array_values(array_unique($cssMatches[1] ?? []));
        $js  = array_values(array_unique($jsMatches[1] ?? []));

        return [
            'status' => 'ok',
            'url'    => $url,
            'title'  => $title,
            'shell'  => str_contains($html, 'HIVE-SHELL:MAIN') ? 'main' : 'home',
            'html'   => $content,
            'css'    => $css,
            'js'     => $js,
        ];
    }

    private function between(string $haystack, string $start, string $end): ?string
    {
        $s = strpos($haystack, $start);
        if ($s === false) {
            return null;
        }

        $s += strlen($start);
        $e = strpos($haystack, $end, $s);

        if ($e === false) {
            return null;
        }

        return substr($haystack, $s, $e - $s);
    }
}
