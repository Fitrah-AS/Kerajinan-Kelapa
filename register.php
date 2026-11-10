<?php
require_once __DIR__ . '/app.php';
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirm'] ?? '');

    if ($name === '' || strlen($name) > 80) {
        $error = 'Nama wajib diisi (maksimal 80 karakter).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi harus memiliki minimal 8 karakter.';
    } elseif ($password !== $confirmation) {
        $error = 'Konfirmasi kata sandi tidak sama.';
    } else {
        $users = store_read('users.json');
        $exists = false;
        foreach ($users as $user) {
            if (strtolower((string)($user['email'] ?? '')) === $email) {
                $exists = true;
                break;
            }
        }
        if ($exists) {
            $error = 'Email sudah terdaftar. Silakan masuk.';
        } else {
            $newUser = [
                'id' => bin2hex(random_bytes(12)),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => '',
                'address' => '',
                'created_at' => date(DATE_ATOM),
            ];
            $users[] = $newUser;
            store_write('users.json', $users);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUser['id'];
            $_SESSION['user_email'] = $newUser['email'];
            $_SESSION['user_name'] = $newUser['name'];
            save_cart();
            redirect('profile.php?baru=1');
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Buat Akun — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body class="auth-page">
<main class="auth-layout">
    <a class="brand auth-brand" href="index.php"><span class="brand-mark">K</span><span><strong>Ruang Kelapa</strong><small>Kerajinan lokal, cerita alam</small></span></a>
    <section class="auth-card">
        <a class="back-link" href="index.php">← Kembali ke toko</a>
        <span class="eyebrow">Bergabung bersama kami</span><h1>Buat akun baru</h1><p class="muted">Simpan data pengiriman dan lacak pesanan dalam satu tempat.</p>
        <?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="name">Nama lengkap</label><input id="name" name="name" autocomplete="name" maxlength="80" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Nama kamu">
            <label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com">
            <label for="password">Kata sandi <small>(minimal 8 karakter)</small></label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required placeholder="Buat kata sandi">
            <label for="password_confirm">Ulangi kata sandi</label><input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" required placeholder="Ketik ulang kata sandi">
            <button class="button button-full" type="submit">Buat akun</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </section>
    <p class="auth-caption">Akunmu digunakan untuk menyimpan keranjang dan pesanan.</p>
</main>
</body>
</html>
