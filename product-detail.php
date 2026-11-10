<?php
require_once __DIR__ . '/app.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || !isset($all_products[$id])) {
    http_response_code(404);
    $product = null;
} else {
    $product = $all_products[$id];
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $product ? e($product['title']) . ' — Ruang Kelapa' : 'Produk tidak ditemukan — Ruang Kelapa' ?></title><link rel="stylesheet" href="css/style.css"></head>
<body>
<?php render_header(); ?>
<main class="page-section wrap">
    <?php if (!$product): ?>
        <div class="empty-state"><span>⌕</span><h1>Produk tidak ditemukan</h1><p>Produk yang kamu cari mungkin sudah tidak tersedia.</p><a class="button" href="index.php#produk">Kembali ke katalog</a></div>
    <?php else: ?>
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Beranda</a><span>/</span><a href="index.php#produk">Produk</a><span>/</span><span><?= e($product['title']) ?></span></nav>
        <div class="detail-layout">
            <div class="detail-art product-art art-<?= (int)$product['id'] ?>"><span><?= e($product['category']) ?></span><i aria-hidden="true"></i></div>
            <section class="detail-copy">
                <span class="eyebrow"><?= e($product['category']) ?> · Karya pengrajin lokal</span>
                <h1><?= e($product['title']) ?></h1>
                <div class="detail-rating"><span>★★★★★</span> <?= e($product['rating']) ?> <small>(<?= (int)$product['reviews'] ?> ulasan)</small></div>
                <p class="detail-price"><?= rupiah(product_price($product)) ?></p>
                <p class="stock-note"><?= (int)$product['stock'] > 0 ? 'Tersedia · ' . (int)$product['stock'] . ' stok' : 'Stok habis' ?></p>
                <p class="detail-description"><?= e($product['description']) ?></p>
                <div class="spec-list"><h2>Detail produk</h2><ul><?php foreach ($product['details'] as $detail): ?><li><?= e($detail) ?></li><?php endforeach; ?></ul></div>
                <?php if ((int)$product['stock'] > 0): ?>
                <form class="detail-actions" method="post" action="cart.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                    <label class="quantity-label" for="qty">Jumlah</label><input class="quantity-input" type="number" id="qty" name="qty" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                    <button class="button" type="submit">Tambah ke keranjang</button>
                    <button class="button button-outline" name="buy_now" value="1" type="submit">Beli sekarang</button>
                </form>
                <?php else: ?><button class="button" disabled>Stok habis</button><?php endif; ?>
                <p class="shipping-hint">Pengiriman flat <?= rupiah(20000) ?> per pesanan · Pembayaran diproses sebagai simulasi.</p>
            </section>
        </div>
    <?php endif; ?>
</main>
<?php render_footer(); ?>
</body>
</html>
