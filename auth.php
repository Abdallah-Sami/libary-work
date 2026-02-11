<?php
// نظام حماية مخصص لـ InfinityFree بدون Sessions
error_reporting(0);

define('VALID_USERNAME', 'Eid2025');
define('VALID_PASSWORD', 'Asd741852');
define('AUTH_COOKIE', 'lib_auth');
define('SECRET', 'Xcrc76817'); 

function createToken($user) {
    $time = time();
    $data = $user . '|' . $time;
    $hash = hash_hmac('sha256', $data, SECRET);
    return base64_encode($data . '|' . $hash);
}

function verifyToken($token) {
    if (empty($token)) return false;
    $decoded = @base64_decode($token);
    if (!$decoded) return false;
    $parts = explode('|', $decoded);
    if (count($parts) !== 3) return false;
    list($user, $time, $hash) = $parts;
    $expected = hash_hmac('sha256', $user . '|' . $time, SECRET);
    if (!hash_equals($expected, $hash)) return false;
    if ((time() - $time) > (7 * 86400)) return false;
    return $user;
}

function isLoggedIn() {
    return isset($_COOKIE[AUTH_COOKIE]) && verifyToken($_COOKIE[AUTH_COOKIE]) !== false;
}

function login($user, $pass) {
    if ($user === VALID_USERNAME && $pass === VALID_PASSWORD) {
        $token = createToken($user);
        setcookie(AUTH_COOKIE, $token, time() + (7 * 86400), '/', '', false, true);
        return true;
    }
    return false;
}

function logout() {
    setcookie(AUTH_COOKIE, '', time() - 3600, '/', '', false, true);
}

function getUser() {
    if (!isset($_COOKIE[AUTH_COOKIE])) return null;
    return verifyToken($_COOKIE[AUTH_COOKIE]);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
