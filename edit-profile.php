<?php
session_start();

// 🔒 Proteksi
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$sukses = '';
$file = 'users.json';

// Baca users
$users = json_decode(file_get_contents($file), true) ?? [];
$currentUser = null;
$currentIndex = -1;

foreach ($users as $i => $u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $currentUser = $u;
        $currentIndex = $i;
        break;
    }
}

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordBaru = $_POST['password_baru'] ?? '';
    $passwordLama = $_POST['password_lama'] ?? '';

    // Validasi
    if (empty($nama) || empty($email)) {
        $error = "Nama dan email wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (!empty($passwordBaru) && strlen($passwordBaru) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } elseif (!empty($passwordBaru) && !password_verify($passwordLama, $currentUser['password'])) {
        $error = "Password lama salah!";
    } else {
        // Cek duplikasi email (kecuali milik sendiri)
        $duplikat = false;
        foreach ($users as $u) {
            if ($u['id'] !== $currentUser['id'] && strtolower($u['email']) === strtolower($email)) {
                $duplikat = true;
                break;
            }
        }

        if ($duplikat) {
            $error = "Email sudah dipakai user lain!";
        } else {
            // Sanitasi
            $nama = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            // Update data
            $users[$currentIndex]['nama'] = $nama;
            $users[$currentIndex]['email'] = $email;
            $users[$currentIndex]['updated_at'] = date('Y-m-d H:i:s');

            // Update password jika diisi
            if (!empty($passwordBaru)) {
                $users[$currentIndex]['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
            }

            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Update session
            $_SESSION['username'] = $nama;
            $_SESSION['email'] = $email;

            $sukses = "Profil berhasil diupdate!";
            $currentUser = $users[$currentIndex];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>✏️ Edit Profile</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($sukses): ?>
            <p class="sukses"><?= htmlspecialchars($sukses) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($currentUser['nama']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required>

            <hr style="margin:20px 0;">
            <p style="font-size:12px;color:#888;">Kosongkan jika tidak ingin ganti password</p>

            <label>Password Lama</label>
            <input type="password" name="password_lama" placeholder="Password lama">

            <label>Password Baru</label>
            <input type="password" name="password_baru" placeholder="Password baru (min 6 karakter)">

            <button type="submit">Simpan Perubahan</button>
        </form>

        <p><a href="dashboard.php">← Kembali ke Dashboard</a></p>
    </div>
</body>
</html>