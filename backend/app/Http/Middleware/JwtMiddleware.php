<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $auth_header = $request->header('Authorization');
        if (!$auth_header) {
            return response()->json(['error' => 'Abscent TOKEN'], 400);
        }
        if (!preg_match('/^Bearer\s+\S+$/', $auth_header)) {
            return response()->json(['error' => 'Invalid TOKEN'], 401);
        }
        try {
            $key = config('app.jwt_key');
            $token = str_replace('Bearer ', '', $auth_header); 
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $user = User::find($decoded->data->id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }
            Auth::login($user);
            return $next($request);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            if (str_contains($error_message, 'SQLSTATE')) {
                return response()->json(['error' => 'Invalid TOKEN: ' . $error_message], 400);
            }
            return response()->json(['error' => 'Invalid TOKEN: ' . $error_message], 401);
        }
    }    
}
