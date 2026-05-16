<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track homepage only
        if (! $request->routeIs('frontend.home')) {
            return $response;
        }

        // Only count normal GET page visits
        if (! $request->isMethod('GET')) {
            return $response;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return $response;
        }

        // Avoid prefetch / preview / prerender duplicate noise
        $purpose = strtolower((string) $request->headers->get('Purpose'));
        $secPurpose = strtolower((string) $request->headers->get('Sec-Purpose'));
        $xMoz = strtolower((string) $request->headers->get('X-Moz'));

        if (
            str_contains($purpose, 'prefetch') ||
            str_contains($purpose, 'preview') ||
            str_contains($secPurpose, 'prefetch') ||
            str_contains($secPurpose, 'preview') ||
            str_contains($xMoz, 'prefetch')
        ) {
            return $response;
        }

        if (! $request->hasSession()) {
            return $response;
        }

        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        $sessionId = $request->session()->getId();
        $routeName = optional($request->route())->getName();
        $requestPath = '/' . ltrim($request->path(), '/');
        $today = now()->toDateString();

        VisitorLog::query()->updateOrCreate(
            [
                'visit_date'   => $today,
                'session_id'   => $sessionId,
                'route_name'   => $routeName,
                'request_path' => $requestPath,
            ],
            [
                'user_id'        => optional($request->user())->id,
                'member_id'      => optional($request->user())->member_id,
                'request_method' => $request->method(),
                'ip_address'     => $request->ip(),
                'user_agent'     => str($request->userAgent() ?? '')->limit(500)->toString(),
                'visited_at'     => now(),
                'source'         => $request->user() ? 'authenticated_web' : 'public_web',
                'is_dashboard'   => false,
            ]
        );

        return $response;
    }
}