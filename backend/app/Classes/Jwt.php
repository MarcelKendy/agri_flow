<?php

namespace App\Classes;

use App\Models\User;
use Firebase\JWT\JWT as JWTFirebase;
use Firebase\JWT\Key;

class Jwt {
    public static function validateToken($token) {
        $key = new Key(config('app.jwt_key'), 'HS256');
        try {
            $decoded = JWTFirebase::decode($token, $key);
            return response()->json($decoded, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 401);
        }
    }
    public static function create(User $data) {
        $key = config('app.jwt_key');
        $payload = [
            "exp" => time() + 604800, //604800s = 1sem
            "iat" => time(),
            "data" => $data
        ];
        return JWTFirebase::encode($payload, $key, 'HS256');
    }
}