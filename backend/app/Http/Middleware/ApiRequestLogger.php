<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiLog;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $this->tryHydrateUserFromBearer($request);
        $response = $next($request);
        if ($request->isMethod('OPTIONS')) {
            return $response;
        }
        $agent = new Agent();
        $user = $request->user() ?? Auth::user();

        ApiLog::create([
            'user_id'            => optional($user)->id,
            'user_ip'            => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'device'             => $agent->device(),
            'browser'            => $agent->browser(),
            'os'                 => $agent->platform(),
            'controller_method'  => optional($request->route())->getActionName(),
            'request_url'        => $request->fullUrl(),
            'request_method'     => $request->method(),
            'request_parameters' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
            'request_headers'    => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE),
        ]);

        return $response;
    }

    /**
     * Try to set the authenticated user from a Bearer token if present.
     * Never throws or aborts—silent best-effort.
     */
    protected function tryHydrateUserFromBearer(Request $request): void
    {
        if (auth()->check() || $request->user()) {
            return;
        }
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !preg_match('/^Bearer\s+(\S+)$/', $authHeader, $m)) {
            return;
        }
        if ($request->attributes->get('jwt_optional_hydrated')) {
            return;
        }
        try {
            $token = $m[1];
            $key   = config('app.jwt_key');
            if (!$key) return;
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
            $userId  = $decoded->data->id ?? null;
            if (!$userId) return;
            $user = \App\Models\User::find($userId);
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user);
                $request->setUserResolver(fn() => $user);
                $request->attributes->set('jwt_optional_hydrated', true);
            }
        } catch (\Throwable $e) {
            return;
        }
    }
}
