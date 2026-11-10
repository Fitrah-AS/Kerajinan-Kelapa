<?php
require_once __DIR__ . '/app.php';
$cart = cart_contents();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($action === 'add' && $id !== false && isset($all_products[$id])) {
        $quantity = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_INT);
        $quantity = $quantity === false ? 0 : $quantity;
        if ($quantity < 1 || $quantity + (int)($cart[$id] ?? 0) > (int)$all_products[$id]['stock']) {
            $error = 'Jumlah melebihi stok yang tersedia.';
        } else {
            $cart[$id] = (int)($cart[$id] ?? 0) + $quantity;
            $_SESSION['cart'] = $cart;
            save_cart();
            if (($_POST['buy_now'] ?? '') === '1') {
                if (!is_logged_in()) {
                    $_SESSION['after_login'] = 'checkout.php';
                    flash('Masuk atau buat akun untuk melanjutkan checkout.');
                    redirect('login.php');
                }
                redirect('checkout.php');
            }
            flash('Produk berhasil ditambahkan ke keranjang.');
            redirect('cart.php');
        }
    } elseif ($action === 'update' && $id !== false && isset($all_products[$id])) {
        $quantity = filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 1 || $quantity > (int)$all_products[$id]['stock']) {
            $error = 'Jumlah harus antara 1 dan stok yang tersedia.';
        } else {
            $cart[$id] = $quantity;
            $_SESSION['cart'] = $cart;
            save_cart();
            redirect('cart.php');
        }
    } elseif ($action === 'remove' && $id !== false) {
        unset($cart[$id]);
        $_SESSION['cart'] = $cart;
        save_cart();
        flash('Produk dihapus dari keranjang.');
        redirect('cart.php');
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        save_cart();
        redirect('cart.php');
    } else {
        $error = 'Permintaan keranjang tidak dapat diproses.';
    }
}

$rows = [];
$subtotal = 0;
foreach ($cart as $id => $quantity) {
    if (!isset($all_products[$id]) || (int)$quantity < 1) {
        continue;
    }
    $product = $all_products[$id];
    $lineTotal = product_price($product) * (int)$quantity;
    $subtotal += $lineTotal;
    $rows[] = ['product' => $product, 'qty' => (int)$quantity, 'line_total' => $lineTotal];
}
$shipping = $subtotal > 0 ? 20000 : 0;
$notice = flash();
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Keranjang — Ruang Kelapa</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <div class="page-title"><span class="eyebrow">Ringkasan belanja</span><h1>Keranjangmu</h1><p>Periksa produk dan jumlah sebelum melanjutkan.</p></div>
    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
    <?php if (!$rows): ?>
        <div class="empty-state"><span>♡</span><h2>Keranjang masih kosong</h2><p>Temukan kerajinan yang cocok untuk rumahmu.</p><a class="button" href="index.php#produk">Lihat produk</a></div>
    <?php else: ?>
        <div class="cart-layout">
            <section class="cart-items" aria-label="Produk di keranjang">
                <?php foreach ($rows as $row): $product = $row['product']; ?>
                <article class="cart-item">
                    <a class="product-art art-<?= (int)$product['id'] ?>" href="product-detail.php?id=<?= (int)$product['id'] ?>" aria-label="<?= e($product['title']) ?>"><i aria-hidden="true"></i></a>
                    <div class="cart-item-info"><span class="product-meta"><?= e($product['category']) ?></span><h2><a href="product-detail.php?id=<?= (int)$product['id'] ?>"><?= e($product['title']) ?></a></h2><p><?= rupiah(product_price($product)) ?> / item</p>
                        <form class="quantity-form" method="post" action="cart.php">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <label for="qty-<?= (int)$product['id'] ?>">Jumlah</label><input id="qty-<?= (int)$product['id'] ?>" type="number" name="qty" value="<?= $row['qty'] ?>" min="1" max="<?= (int)$product['stock'] ?>"><button class="text-link" type="submit">Perbarui</button>
                        </form>
                    </div>
                    <strong class="line-total"><?= rupiah($row['line_total']) ?></strong>
                    <form method="post" action="cart.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="remove-button" type="submit" aria-label="Hapus <?= e($product['title']) ?>">Hapus</button></form>
                </article>
                <?php endforeach; ?>
                <a class="text-link continue-link" href="index.php#produk">← Lanjut belanja</a>
            </section>
            <aside class="summary-card">
                <h2>Ringkasan</h2><div class="summary-row"><span>Subtotal (<?= array_sum(array_column($rows, 'qty')) ?> barang)</span><strong><?= rupiah($subtotal) ?></strong></div>
                <div class="summary-row"><span>Pengiriman</span><strong><?= rupiah($shipping) ?></strong></div>
                <div class="summary-row summary-total"><span>Total</span><strong><?= rupiah($subtotal + $shipping) ?></strong></div>
                <p class="muted small">Biaya pengiriman flat <?= rupiah(20000) ?> per pesanan.</p>
                <a class="button button-full" href="checkout.php">Lanjut ke checkout <span aria-hidden="true">→</span></a>
                <form method="post" action="cart.php" class="clear-cart"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="clear"><button class="text-link" type="submit">Kosongkan keranjang</button></form>
            </aside>
        </div>
    <?php endif; ?>
</main>
<?php render_footer(); ?>
</body>
</html>
