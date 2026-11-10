<?php
require_once __DIR__ . '/app.php';

$query = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['kategori']) && is_string($_GET['kategori']) ? $_GET['kategori'] : 'Semua';
$categories = product_categories();
$products = array_filter($all_products, static function (array $product) use ($query, $category): bool {
    $matchesQuery = $query === '' || stripos($product['title'] . ' ' . $product['description'], $query) !== false;
    $matchesCategory = $category === 'Semua' || $product['category'] === $category;
    return $matchesQuery && $matchesCategory;
});
$notice = flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Temukan kerajinan kelapa buatan pengrajin lokal untuk rumah yang lebih hangat dan berkarakter.">
    <title>Ruang Kelapa — Kerajinan Lokal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php render_header('home'); ?>
<main>
    <section class="hero wrap">
        <div class="hero-copy">
            <span class="eyebrow">Dari alam, untuk ruang yang lebih berarti</span>
            <h1>Sentuhan alami.<br><em>Makna yang abadi.</em></h1>
            <p>Kerajinan kelapa pilihan yang dibuat perlahan oleh pengrajin lokal. Hadirkan karakter alami di rumah Anda.</p>
            <a class="button" href="#produk">Jelajahi koleksi <span aria-hidden="true">→</span></a>
            <div class="hero-note"><span class="note-icon">✳</span><span><strong>Dibuat dengan tangan</strong><small>Setiap karya punya ceritanya sendiri</small></span></div>
        </div>
        <div class="hero-art" aria-label="Ilustrasi kerajinan kelapa">
            <div class="sun-disc"></div>
            <div class="hero-leaf leaf-one"></div><div class="hero-leaf leaf-two"></div>
            <div class="hero-bowl"><span></span></div>
            <div class="hero-caption">Karya alam <span>01 / 06</span></div>
        </div>
    </section>

    <section class="benefits wrap" aria-label="Keunggulan">
        <div><span>01</span><strong>Material alami</strong><small>Memanfaatkan kelapa pilihan</small></div>
        <div><span>02</span><strong>Buatan pengrajin</strong><small>Dikerjakan dengan perhatian</small></div>
        <div><span>03</span><strong>Pesan dengan mudah</strong><small>Proses belanja yang jelas</small></div>
    </section>

    <section class="catalog-section" id="produk">
        <div class="wrap">
            <div class="section-heading">
                <div><span class="eyebrow">Koleksi pilihan</span><h2>Temukan karya favoritmu</h2><p>Produk unik dengan kehangatan material alami.</p></div>
                <form class="search-form" method="get" action="index.php#produk">
                    <label class="sr-only" for="search">Cari produk</label>
                    <input id="search" type="search" name="q" value="<?= e($query) ?>" placeholder="Cari kerajinan...">
                    <?php if ($category !== 'Semua'): ?><input type="hidden" name="kategori" value="<?= e($category) ?>"><?php endif; ?>
                    <button type="submit" aria-label="Cari">Cari</button>
                </form>
            </div>
            <div class="category-list" aria-label="Kategori produk">
                <?php foreach ($categories as $label => $value): ?>
                    <a class="<?= $category === $value ? 'selected' : '' ?>" href="index.php?<?= http_build_query(array_filter(['kategori' => $value, 'q' => $query], static fn($v) => $v !== '' && $v !== 'Semua')) ?>#produk"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
            <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a class="product-art art-<?= (int)$product['id'] ?>" href="product-detail.php?id=<?= (int)$product['id'] ?>" aria-label="Lihat <?= e($product['title']) ?>"><span><?= e($product['category']) ?></span><i aria-hidden="true"></i></a>
                        <div class="product-card-body">
                            <div class="product-meta"><?= e($product['category']) ?><span>★ <?= e($product['rating']) ?></span></div>
                            <h3><a href="product-detail.php?id=<?= (int)$product['id'] ?>"><?= e($product['title']) ?></a></h3>
                            <div class="product-buy"><strong><?= rupiah(product_price($product)) ?></strong><a href="product-detail.php?id=<?= (int)$product['id'] ?>" aria-label="Lihat produk <?= e($product['title']) ?>">Lihat detail <span aria-hidden="true">↗</span></a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="empty-state"><span>⌕</span><h3>Produk belum ditemukan</h3><p>Coba kata kunci atau kategori lain.</p><a class="text-link" href="index.php#produk">Hapus filter</a></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="story-banner wrap">
        <div class="story-mark">K</div>
        <div><span class="eyebrow">Berbelanja lebih bermakna</span><h2>Keindahan yang tumbuh dari alam sekitar.</h2><p>Kami percaya bahan yang sederhana bisa menjadi karya istimewa ketika dipadukan dengan keterampilan dan kepedulian.</p></div>
        <a class="button button-light" href="#tentang">Kenali Ruang Kelapa</a>
    </section>
</main>
<?php render_footer(); ?>
</body>
</html>
