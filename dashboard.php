<?php
session_start();

// 🔒 PROTEKSI: Cek login
if (!isset($_SESSION['user_id'])) {
    // Bonus: cek cookie remember_me
   // Bonus: cek cookie remember_token
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $users = json_decode(file_get_contents('users.json'), true) ?? [];
    
    foreach ($users as $u) {
        if (isset($u['remember_token']) && hash_equals($u['remember_token'], $token)) {
            $_SESSION['user_id']  = $u['id'];
            $_SESSION['username'] = $u['nama'];
            $_SESSION['email']    = $u['email'];
            header('Location: dashboard.php');
            exit;
        }
    }
}

    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🏠 Dashboard</h1>
        <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
        <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>

        <a class="btn-logout" href="logout.php">🚪 Logout</a>
        <a class="btn-logout" href="logout.php">🚪 Logout</a>
        <a class="btn-edit" href="edit-profile.php">✏️ Edit Profile</a>
    </div>
</body>
</html>