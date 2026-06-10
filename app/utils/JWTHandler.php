<?php
require_once __DIR__ . '/../vendor/firebase/src/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../vendor/firebase/src/BeforeValidException.php';
require_once __DIR__ . '/../vendor/firebase/src/ExpiredException.php';
require_once __DIR__ . '/../vendor/firebase/src/SignatureInvalidException.php';
require_once __DIR__ . '/../vendor/firebase/src/JWT.php';
require_once __DIR__ . '/../vendor/firebase/src/Key.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JWTHandler
{
    private $secret_key;

    public function __construct()
    {
        $this->secret_key = "HUTECH"; // Thay thế bằng khóa bí mật của bạn
    }

    // Tạo JWT
    public function encode($data)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // jwt valid for 1 hour from the issued time

        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $data
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
