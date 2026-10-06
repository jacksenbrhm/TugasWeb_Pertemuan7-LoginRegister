<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Terima & trim data
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Validasi
    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // 3. Sanitasi
        $nama  = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // 4. Baca data user lama
        $file = 'users.json';
        $users = [];
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $users = json_decode($json, true) ?? [];
        }

        // 5. Cek duplikasi email
        $duplikat = false;
        foreach ($users as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $duplikat = true;
                break;
            }
        }

        if ($duplikat) {
            $error = "Email sudah terdaftar!";
        } else {
            // 6. Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 7. Tambah user baru
            $newUser = [
                'id'         => count($users) + 1,
                'nama'       => $nama,
                'email'      => $email,
                'password'   => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $users[] = $newUser;

            // 8. Simpan kembali ke JSON
            file_put_contents(
                $file,
                json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // 9. Redirect ke login
            header('Location: login.php?msg=registered');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>📝 Registrasi</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" placeholder="Nama Lengkap" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="Email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Password (min 6 karakter)" required>

            <button type="submit">Daftar</button>
        </form>

        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</body>
</html>