<?php
declare(strict_types=1);

function config(): array {
    static $cfg;
    if ($cfg === null) {
        $file = __DIR__ . '/config.php';
        if (!is_file($file)) {
            throw new RuntimeException('Missing config.php. Copy config.example.php to config.php.');
        }
        $cfg = require $file;
    }
    return $cfg;
}

function start_secure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

function csrf_token(): string {
    start_secure_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function require_csrf(): void {
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) json_response(['error'=>'Invalid CSRF token'], 403);
}

function json_response(array $data, int $status=200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    if (!is_array($data)) json_response(['error'=>'Invalid JSON body'], 400);
    return $data;
}

function clean_path(string $path): string {
    if ($path === '' || $path[0] !== '/' || str_contains($path, '..') || str_contains($path, '://')) {
        throw new InvalidArgumentException('Invalid API path.');
    }
    return $path;
}

function api_request(string $method, string $path, ?array $body=null, array $query=[], bool $auth=true): array {
    $cfg = config();
    start_secure_session();
    $url = rtrim($cfg['api_base_url'], '/') . clean_path($path);
    if ($query) $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    if ($auth) {
        $token = $_SESSION['elink_token'] ?? '';
        if ($token === '') return ['status'=>401, 'data'=>['error'=>'Not logged in']];
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => (int)$cfg['timeout_seconds'],
        CURLOPT_SSL_VERIFYPEER => (bool)$cfg['verify_tls'],
        CURLOPT_SSL_VERIFYHOST => $cfg['verify_tls'] ? 2 : 0,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES));
    $raw = curl_exec($ch);
    if ($raw === false) {
        $msg = curl_error($ch); curl_close($ch);
        return ['status'=>502, 'data'=>['error'=>'API connection failed', 'detail'=>$msg]];
    }
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $decoded = json_decode($raw, true);
    return ['status'=>$status, 'data'=>is_array($decoded) ? $decoded : ['raw'=>$raw]];
}
