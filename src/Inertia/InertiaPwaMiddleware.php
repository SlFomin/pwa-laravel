<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Inertia;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InertiaPwaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (InertiaDetector::isInertiaRequest($request)) {
            $response->headers->set('Vary', 'X-Inertia, Accept');
            $response->headers->set('X-PWA-Inertia', '1');

            // Запрещаем SW кешировать Inertia partial responses
            $existing = $response->headers->get('Cache-Control', '');
            if (! str_contains((string) $existing, 'no-store')) {
                $response->headers->set('Cache-Control', trim($existing.', no-store', ', '));
            }
        }

        return $response;
    }
}
