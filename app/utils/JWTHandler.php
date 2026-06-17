<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JWTHandler
{
    private $secret_key;

    public function __construct()
    {
        $this->secret_key = "HUTECH"; // Thay thế bằng khóa bí mật của bạn
    }

    // Tạo Access JWT
    public function encode($data)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 900; // access token valid for 15 minutes

        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $data
        );

        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Tạo Refresh JWT
    public function encodeRefresh($data)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 604800; // refresh token valid for 7 days

        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $data,
            'is_refresh' => true
        );

        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Giải mã JWT
    public function decode($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
