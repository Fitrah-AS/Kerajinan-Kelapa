<?php
require_once __DIR__ . '/app.php';
require_login();
$users = store_read('users.json');
$userIndex = null;
foreach ($users as $index => $user) {
    if ((string)($user['id'] ?? '') === current_user()['id']) {
        $userIndex = $index;
        break;
    }
}
if ($userIndex === null) {
    http_response_code(404);
    exit('Akun tidak ditemukan.');
}
$account = $users[$userIndex];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    if ($name === '' || strlen($name) > 80 || strlen($phone) > 30 || strlen($address) > 500) {
        $error = 'Nama wajib diisi. Periksa panjang data yang dimasukkan.';
    } else {
        $users[$userIndex]['name'] = $name;
        $users[$userIndex]['phone'] = $phone;
        $users[$userIndex]['address'] = $address;
        store_write('users.json', $users);
        $_SESSION['user_name'] = $name;
        flash('Profil berhasil diperbarui.');
        redirect('profile.php');
    }
}
$notice = flash();
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Profil — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <div class="page-title"><span class="eyebrow">Akunmu</span><h1>Profil</h1><p>Perbarui informasi kontak dan alamat untuk checkout lebih cepat.</p></div>
    <?php if (isset($_GET['baru'])): ?><div class="notice notice-info">Akun berhasil dibuat. Lengkapi detail pengiriman supaya checkout lebih cepat.</div><?php endif; ?>
    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?><?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
    <div class="profile-layout"><form class="form-card profile-form" method="post" action="profile.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="name">Nama lengkap</label><input id="name" name="name" maxlength="80" required value="<?= e($_POST['name'] ?? $account['name'] ?? '') ?>">
        <label for="email">Email</label><input id="email" type="email" value="<?= e($account['email']) ?>" disabled><small class="muted">Email tidak dapat diubah dari halaman ini.</small>
        <label for="phone">Nomor telepon</label><input id="phone" name="phone" type="tel" maxlength="30" value="<?= e($_POST['phone'] ?? $account['phone'] ?? '') ?>">
        <label for="address">Alamat pengiriman</label><textarea id="address" name="address" rows="4" maxlength="500"><?= e($_POST['address'] ?? $account['address'] ?? '') ?></textarea>
        <button class="button" type="submit">Simpan perubahan</button>
    </form><aside class="profile-links"><h2>Kelola akun</h2><a href="orders.php">Pesanan saya <span>→</span></a><a href="cart.php">Keranjang <span>→</span></a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="text-link" type="submit">Keluar dari akun</button></form></aside></div>
</main>
<?php render_footer(); ?>
</body>
</html>
