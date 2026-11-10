<?php
require_once __DIR__ . '/app.php';
require_login();
$orders = store_read('orders.json');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (string)($_POST['order_id'] ?? '');
    foreach ($orders as &$order) {
        if (($order['id'] ?? '') === $orderId && ($order['user_id'] ?? '') === current_user()['id']) {
            if (($order['status'] ?? '') !== 'Menunggu pembayaran') {
                $error = 'Pesanan ini sudah diproses dan tidak dapat dibatalkan.';
            } else {
                $order['status'] = 'Dibatalkan pelanggan';
                $order['cancelled_at'] = date(DATE_ATOM);
            }
            break;
        }
    }
    unset($order);
    if ($error === '') {
        store_write('orders.json', $orders);
        flash('Status pesanan berhasil diperbarui.');
        redirect('orders.php');
    }
}
$mine = array_values(array_filter($orders, static fn(array $order): bool => ($order['user_id'] ?? '') === current_user()['id']));
$mine = array_reverse($mine);
$notice = flash();
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pesanan Saya — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <div class="page-title"><span class="eyebrow">Aktivitas akun</span><h1>Pesanan saya</h1><p>Lihat detail dan status pesanan yang pernah kamu buat.</p></div>
    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?><?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
    <?php if (!$mine): ?><div class="empty-state"><span>⌑</span><h2>Belum ada pesanan</h2><p>Pesananmu akan muncul di sini setelah checkout.</p><a class="button" href="index.php#produk">Mulai belanja</a></div>
    <?php else: ?><div class="order-list">
        <?php foreach ($mine as $order): ?>
        <article class="order-card">
            <header><div><span class="product-meta">Dibuat <?= e(date('d M Y, H:i', strtotime($order['created_at']))) ?></span><h2><?= e($order['id']) ?></h2></div><span class="status-pill"><?= e($order['status']) ?></span></header>
            <div class="order-lines"><?php foreach ($order['items'] as $item): ?><div><span><?= e($item['title']) ?> × <?= (int)$item['qty'] ?></span><strong><?= rupiah((int)$item['line_total']) ?></strong></div><?php endforeach; ?></div>
            <div class="order-bottom"><div><span><?= $order['payment'] === 'transfer' ? 'Transfer bank (simulasi)' : 'Bayar di tempat' ?></span><strong>Total <?= rupiah((int)$order['total']) ?></strong></div>
                <?php if (($order['status'] ?? '') === 'Menunggu pembayaran'): ?><form method="post" action="orders.php" onsubmit="return confirm('Batalkan pesanan ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="order_id" value="<?= e($order['id']) ?>"><button class="remove-button" type="submit">Batalkan pesanan</button></form><?php endif; ?>
            </div>
            <details><summary>Alamat pengiriman</summary><p><?= e($order['customer']['name']) ?> · <?= e($order['customer']['phone']) ?><br><?= nl2br(e($order['customer']['address'])) ?></p></details>
        </article>
        <?php endforeach; ?>
    </div><?php endif; ?>
</main>
<?php render_footer(); ?>
</body>
</html>
