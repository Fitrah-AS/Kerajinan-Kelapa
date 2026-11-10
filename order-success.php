<?php
require_once __DIR__ . '/app.php';
require_login();
$orderId = (string)($_SESSION['last_order_id'] ?? '');
unset($_SESSION['last_order_id']);
if ($orderId === '') {
    redirect('orders.php');
}
$orders = store_read('orders.json');
$order = null;
foreach ($orders as $candidate) {
    if (($candidate['id'] ?? '') === $orderId && ($candidate['user_id'] ?? '') === current_user()['id']) {
        $order = $candidate;
        break;
    }
}
if (!$order) {
    redirect('orders.php');
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pesanan dibuat — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <section class="success-card">
        <div class="success-icon">✓</div><span class="eyebrow">Pesanan berhasil dibuat</span><h1>Terima kasih sudah mendukung pengrajin lokal.</h1>
        <p>Nomor pesananmu <strong><?= e($order['id']) ?></strong>. Simpan nomor ini untuk melihat status pesanan.</p>
        <?php if ($order['payment'] === 'transfer'): ?><div class="notice notice-info"><strong>Instruksi pembayaran simulasi</strong><br>Silakan konfirmasi pembayaran kepada penjual melalui email halo@ruangkelapa.id. Fitur ini belum terhubung ke bank atau payment gateway.</div><?php else: ?><div class="notice notice-info">Metode bayar di tempat dipilih. Penjual akan menghubungimu untuk konfirmasi.</div><?php endif; ?>
        <div class="success-actions"><a class="button" href="orders.php">Lihat pesanan</a><a class="text-link" href="index.php#produk">Kembali belanja</a></div>
    </section>
</main>
<?php render_footer(); ?>
</body>
</html>
