<?php
require_once __DIR__ . '/../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
            PDO::ATTR_STRINGIFY_FETCHES=>false,
        ]);
    }
    return $pdo;
}
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_valid(): bool { return isset($_POST['csrf'],$_SESSION['csrf']) && hash_equals($_SESSION['csrf'],(string)$_POST['csrf']); }
function login_key(string $identifier): string {
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash('sha256', strtolower(trim($identifier)).'|'.$ip.'|'.AUTH_PEPPER);
}
function login_is_limited(string $key): bool {
    $q=db()->prepare('SELECT attempts, last_attempt_at FROM auth_attempts WHERE attempt_key=?'); $q->execute([$key]); $r=$q->fetch();
    if (!$r) return false;
    if (strtotime((string)$r['last_attempt_at']) < time()-15*60) { db()->prepare('DELETE FROM auth_attempts WHERE attempt_key=?')->execute([$key]); return false; }
    return (int)$r['attempts'] >= 8;
}
function record_login_failure(string $key): void {
    $q=db()->prepare('INSERT INTO auth_attempts(attempt_key, attempts, last_attempt_at) VALUES(?,1,NOW()) ON DUPLICATE KEY UPDATE attempts=IF(last_attempt_at < NOW()-INTERVAL 15 MINUTE,1,attempts+1), last_attempt_at=NOW()'); $q->execute([$key]);
}
function clear_login_failures(string $key): void { db()->prepare('DELETE FROM auth_attempts WHERE attempt_key=?')->execute([$key]); }
function audit_event(?int $userId, string $event, array $meta=[]): void {
    try { $q=db()->prepare('INSERT INTO audit_log(user_id,event_name,meta_json,created_at) VALUES(?,?,?,NOW())'); $q->execute([$userId,$event,json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]); } catch (Throwable $ignored) {}
}
