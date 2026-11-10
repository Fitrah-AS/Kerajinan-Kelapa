<?php
require_once __DIR__ . '/app.php';
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$notice = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Masukkan email dan kata sandi yang valid.';
    } else {
        $users = store_read('users.json');
        $user = null;
        foreach ($users as $candidate) {
            if (strtolower((string)($candidate['email'] ?? '')) === $email) {
                $user = $candidate;
                break;
            }
        }
        if (!$user || !password_verify($password, (string)($user['password'] ?? ''))) {
            $error = 'Email atau kata sandi tidak cocok.';
        } else {
            $guestCart = cart_contents();
            session_regenerate_id(true);
            $_SESSION['user_id'] = (string)$user['id'];
            $_SESSION['user_email'] = (string)$user['email'];
            $_SESSION['user_name'] = (string)($user['name'] ?? '');
            unset($_SESSION['cart']);
            $savedCart = cart_contents();
            foreach ($guestCart as $productId => $quantity) {
                if (isset($all_products[$productId])) {
                    $savedCart[$productId] = min(
                        (int)$all_products[$productId]['stock'],
                        (int)($savedCart[$productId] ?? 0) + (int)$quantity
                    );
                }
            }
            $_SESSION['cart'] = $savedCart;
            save_cart();
            $destination = $_SESSION['after_login'] ?? 'index.php';
            unset($_SESSION['after_login']);
            redirect(in_array($destination, ['checkout.php', 'orders.php', 'profile.php', 'cart.php', 'index.php'], true) ? $destination : 'index.php');
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body class="auth-page">
<main class="auth-layout">
    <a class="brand auth-brand" href="index.php"><span class="brand-mark">K</span><span><strong>Ruang Kelapa</strong><small>Kerajinan lokal, cerita alam</small></span></a>
    <section class="auth-card">
        <a class="back-link" href="index.php">← Kembali ke toko</a>
        <span class="eyebrow">Selamat datang kembali</span><h1>Masuk ke akunmu</h1><p class="muted">Lanjutkan belanja dan pantau pesananmu.</p>
        <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com">
            <label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan kata sandi">
            <button class="button button-full" type="submit">Masuk</button>
        </form>
        <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </section>
    <p class="auth-caption">Belanja karya lokal dengan alur yang sederhana dan jelas.</p>
</main>
</body>
</html>
