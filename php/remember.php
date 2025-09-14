<?php
// remember.php - minimal implementation to avoid fatal includes and optionally support remember-me tokens.
// Uses auth_tokens table created in db.php.

if (!isset($mysqli)) {
    require_once __DIR__ . '/db.php';
}

// Create a persistent login token (selector + validator) and set cookie
function create_remember_token(int $user_id, mysqli $mysqli): void {
    $selector = bin2hex(random_bytes(6)); // 12 hex chars
    $validator = bin2hex(random_bytes(16)); // 32 bytes hex = 64 chars
    $validator_hash = hash('sha256', $validator);
    $expires = (new DateTime('+14 days'))->format('Y-m-d H:i:s');

    $stmt = $mysqli->prepare('INSERT INTO auth_tokens (user_id, selector, validator_hash, expires_at) VALUES (?,?,?,?)');
    if ($stmt) {
        $stmt->bind_param('isss', $user_id, $selector, $validator_hash, $expires);
        $stmt->execute();
        $stmt->close();
        // Cookie: selector:validator
        $cookieValue = $selector . ':' . $validator;
        setcookie('rh_auth', $cookieValue, time() + 60*60*24*14, '/', '', false, true);
    }
}

// Validate remember-me cookie and restore session if valid
function attempt_remember_login(mysqli $mysqli): bool {
    if (isset($_SESSION['user_id'])) {
        return true; // already logged in
    }
    if (empty($_COOKIE['rh_auth'])) {
        return false;
    }
    $parts = explode(':', $_COOKIE['rh_auth']);
    if (count($parts) !== 2) return false;
    [$selector, $validator] = $parts;
    if ($selector === '' || $validator === '') return false;

    $stmt = $mysqli->prepare('SELECT auth_tokens.user_id, auth_tokens.validator_hash, auth_tokens.expires_at, users.username, users.role FROM auth_tokens JOIN users ON users.id = auth_tokens.user_id WHERE selector = ? LIMIT 1');
    if (!$stmt) return false;
    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) return false;
    if (new DateTime($row['expires_at']) < new DateTime()) return false;
    if (!hash_equals($row['validator_hash'], hash('sha256', $validator))) return false;

    // Success: restore session
    $_SESSION['user_id'] = (int)$row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    // Also refresh display cookie
    setcookie('rh_username', rawurlencode($row['username']), time()+3600, '/');
    return true;
}

// Revoke tokens for a user (used on logout)
function revoke_remember_tokens(int $user_id, mysqli $mysqli): void {
    $stmt = $mysqli->prepare('DELETE FROM auth_tokens WHERE user_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    }
    setcookie('rh_auth', '', time()-3600, '/');
}
?>