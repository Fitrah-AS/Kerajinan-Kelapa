<?php
require_once __DIR__ . '/app.php';
require_login();

$cart = cart_contents();
$rows = [];
$subtotal = 0;
foreach ($cart as $id => $quantity) {
    if (!isset($all_products[$id]) || (int)$quantity < 1 || (int)$quantity > (int)$all_products[$id]['stock']) {
        continue;
    }
    $product = $all_products[$id];
    $lineTotal = product_price($product) * (int)$quantity;
    $subtotal += $lineTotal;
    $rows[] = ['product' => $product, 'qty' => (int)$quantity, 'line_total' => $lineTotal];
}
if (!$rows) {
    flash('Tambahkan produk ke keranjang sebelum checkout.');
    redirect('cart.php');
}

$users = store_read('users.json');
$userIndex = null;
foreach ($users as $index => $user) {
    if ((string)($user['id'] ?? '') === current_user()['id']) {
        $userIndex = $index;
        break;
    }
}
$account = $userIndex !== null ? $users[$userIndex] : [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $payment = (string)($_POST['payment'] ?? '');
    if ($name === '' || strlen($name) > 80 || $phone === '' || strlen($phone) > 30 || $address === '' || strlen($address) > 500) {
        $error = 'Lengkapi nama, nomor telepon, dan alamat pengiriman.';
    } elseif (!in_array($payment, ['transfer', 'cod'], true)) {
        $error = 'Pilih metode pembayaran yang tersedia.';
    } else {
        $shipping = 20000;
        $order = [
            'id' => 'RK-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => current_user()['id'],
            'created_at' => date(DATE_ATOM),
            'status' => 'Menunggu pembayaran',
            'customer' => ['name' => $name, 'email' => current_user()['email'], 'phone' => $phone, 'address' => $address],
            'payment' => $payment,
            'items' => array_map(static fn(array $row): array => [
                'id' => (int)$row['product']['id'],
                'title' => $row['product']['title'],
                'price' => product_price($row['product']),
                'qty' => $row['qty'],
                'line_total' => $row['line_total'],
            ], $rows),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
        ];
        $orders = store_read('orders.json');
        $orders[] = $order;
        store_write('orders.json', $orders);

        if ($userIndex !== null) {
            $users[$userIndex]['name'] = $name;
            $users[$userIndex]['phone'] = $phone;
            $users[$userIndex]['address'] = $address;
            store_write('users.json', $users);
            $_SESSION['user_name'] = $name;
        }
        $_SESSION['cart'] = [];
        save_cart();
        $_SESSION['last_order_id'] = $order['id'];
        redirect('order-success.php');
    }
}
$shipping = 20000;
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Checkout — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <div class="page-title"><span class="eyebrow">Langkah terakhir</span><h1>Checkout</h1><p>Isi alamat pengiriman dan pilih metode pembayaran.</p></div>
    <div class="checkout-steps"><span class="done">1. Keranjang</span><span class="current">2. Pengiriman & pembayaran</span><span>3. Selesai</span></div>
    <?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="checkout.php" class="checkout-layout">
        <section class="checkout-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-card"><h2>Alamat pengiriman</h2>
                <label for="name">Nama penerima</label><input id="name" name="name" autocomplete="name" maxlength="80" required value="<?= e($_POST['name'] ?? $account['name'] ?? current_user()['name']) ?>">
                <label for="phone">Nomor telepon</label><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="30" required value="<?= e($_POST['phone'] ?? $account['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                <label for="address">Alamat lengkap</label><textarea id="address" name="address" rows="4" maxlength="500" required placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"><?= e($_POST['address'] ?? $account['address'] ?? '') ?></textarea>
            </div>
            <fieldset class="form-card payment-options"><legend>Metode pembayaran</legend>
                <label class="payment-choice"><input type="radio" name="payment" value="transfer" <?= ($_POST['payment'] ?? 'transfer') === 'transfer' ? 'checked' : '' ?>><span><strong>Transfer bank (simulasi)</strong><small>Instruksi pembayaran ditampilkan setelah pesanan dibuat.</small></span></label>
                <label class="payment-choice"><input type="radio" name="payment" value="cod" <?= ($_POST['payment'] ?? '') === 'cod' ? 'checked' : '' ?>><span><strong>Bayar di tempat (COD)</strong><small>Bayar saat pesanan diterima. Konfirmasi akan dilakukan oleh penjual.</small></span></label>
            </fieldset>
        </section>
        <aside class="summary-card"><h2>Pesananmu</h2>
            <?php foreach ($rows as $row): ?><div class="checkout-item"><span><?= e($row['product']['title']) ?> × <?= $row['qty'] ?></span><strong><?= rupiah($row['line_total']) ?></strong></div><?php endforeach; ?>
            <div class="summary-row"><span>Subtotal</span><strong><?= rupiah($subtotal) ?></strong></div><div class="summary-row"><span>Pengiriman</span><strong><?= rupiah($shipping) ?></strong></div>
            <div class="summary-row summary-total"><span>Total pembayaran</span><strong><?= rupiah($subtotal + $shipping) ?></strong></div>
            <p class="notice notice-info small">Pembayaran di situs ini masih berupa simulasi. Tidak ada transaksi bank yang diproses otomatis.</p>
            <button class="button button-full" type="submit">Buat pesanan <span aria-hidden="true">→</span></button>
            <a class="text-link back-to-cart" href="cart.php">← Kembali ke keranjang</a>
        </aside>
    </form>
</main>
<?php render_footer(); ?>
</body>
</html>
