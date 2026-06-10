<?php
require_once __DIR__ . '/JWTHandler.php';

class JWTMiddleware
{
    private static $jwtHandler = null;

    private static function getHandler()
    {
        if (self::$jwtHandler === null) {
            self::$jwtHandler = new JWTHandler();
        }
        return self::$jwtHandler;
    }

    // Xác thực chung cho mọi request cần đăng nhập. Trả về payload đã decode.
    public static function authenticate()
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Missing token']);
            exit;
        }

        $arr = explode(" ", $authHeader);
        $jwt = $arr[1] ?? null;

        if (!$jwt) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Invalid token format']);
            exit;
        }

        $decoded = self::getHandler()->decode($jwt);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Invalid or expired token']);
            exit;
        }

        return $decoded;
    }

    // Xác thực vai trò cụ thể. Trả về payload.
    public static function requireRole($role)
    {
        $decoded = self::authenticate();
        $userRole = $decoded['role'] ?? 'user';

        if ($userRole !== $role) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden: You do not have permission to access this resource']);
            exit;
        }

        return $decoded;
    }
}
?>
