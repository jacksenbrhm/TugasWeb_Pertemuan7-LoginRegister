<?php
session_start();

// Hapus semua session
$_SESSION = [];

// Hapus token remember dari users.json
if (isset($_COOKIE['remember_token']) && isset($_SESSION['user_id'])) {
    $file = 'users.json';
    $users = json_decode(file_get_contents($file), true) ?? [];
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user_id']) {
            unset($u['remember_token']);
            break;
        }
    }
    unset($u);
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Hapus cookie remember_token
setcookie('remember_token', '', time() - 3600, '/');

// Destroy session
session_destroy();

// Redirect ke login
header('Location: login.php?msg=logged_out');
exit;
?>