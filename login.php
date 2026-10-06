<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$sukses = '';

// Pesan sukses dari redirect register
if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $sukses = "Registrasi berhasil! Silakan login.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $sukses = "Anda telah logout.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi!";
    } else {
        // Baca users.json
        $file = 'users.json';
        $users = [];
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $users = json_decode($json, true) ?? [];
        }

        // Cari user berdasarkan email
        $foundUser = null;
        foreach ($users as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $foundUser = $u;
                break;
            }
        }

        // Verifikasi password
        if ($foundUser && password_verify($password, $foundUser['password'])) {
            // Set session
            $_SESSION['user_id']  = $foundUser['id'];
            $_SESSION['username'] = $foundUser['nama'];
            $_SESSION['email']    = $foundUser['email'];

            // Bonus: Remember Me (cookie 30 hari) — VERSI AMAN
            if ($remember) {
                // Generate token random yang aman
                $token = bin2hex(random_bytes(32));

                // Simpan token ke users.json (per user)
                foreach ($users as &$u) {
                    if ($u['id'] === $foundUser['id']) {
                        $u['remember_token'] = $token;
                        break;
                    }
                }
                unset($u);
                file_put_contents(
                    $file,
                    json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                // Set cookie dengan flag keamanan
                setcookie('remember_token', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Email atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>🔐 Login</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($sukses): ?>
            <p class="sukses"><?= htmlspecialchars($sukses) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" placeholder="Email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Password" required>

            <label class="checkbox">
                <input type="checkbox" name="remember"> Remember Me
            </label>

            <button type="submit">Login</button>
        </form>

        <p>Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>
</body>
</html>