<?php
// Include data produk terpusat
require_once 'products-data.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$cart_count = $_SESSION['cart_count'] ?? 0;

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Validasi ID dan ambil data produk
if (!$id || !isset($all_products[$id])) {
	header('Location: beranda.php');
	exit;
}

$product = $all_products[$id];

// Fungsi untuk render bintang rating
function render_stars($rating) {
	$full = floor($rating);
	$half = ($rating - $full) >= 0.5 ? 1 : 0;
	$empty = 5 - $full - $half;
	$stars = str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', $empty);
	return htmlspecialchars($stars);
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo htmlspecialchars($product['title']); ?> - Kerajinan Kelapa</title>
	<?php $ver = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
	<link rel="stylesheet" href="css/style.css?v=<?php echo $ver; ?>">
</head>
<body>
	<!-- Header (sama seperti beranda) -->
	<header class="site-header">
		<div class="container">
			<div class="logo">
				<img src="asset/logo.png" alt="Kerajinan Kelapa" class="logo-img" width="64" height="64">
			</div>
			<nav class="nav" aria-label="Main navigation">
				<ul>
					<li><a href="beranda.php">Beranda</a></li>
					<li><a href="#">Produk</a></li>
					<li><a href="#">Tentang Kami</a></li>
				</ul>
			</nav>
			<div class="actions">
				<button class="btn">Daftar</button>
				<button class="btn login">Login</button>
			</div>
		</div>
		<div class="search-row">
			<div class="search-inner">
				<div class="search-box" role="search">
					<svg class="Search-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M21 21l-4.35-4.35" stroke="#777" stroke-width="2" stroke-linecap="round"/>
						<circle cx="10.5" cy="10.5" r="5.5" stroke="#777" stroke-width="2"/>
					</svg>
					<input type="search" placeholder="Cari kerajinan" aria-label="Cari kerajinan" />
				</div>
				<div class="cart-wrapper">
					<a href="cart.php" class="icon-link" title="Keranjang">
						<img src="asset/keranjang.png" alt="Keranjang" width="20" height="20" />
						<?php if ($cart_count > 0): ?>
							<span class="badge"><?php echo (int)$cart_count; ?></span>
						<?php endif; ?>
					</a>
				</div>
			</div>
		</div>
	</header>

	<!-- Product Detail Section -->
	<main class="product-detail-page">
		<div class="detail-container">
			<!-- Breadcrumb -->
			<nav class="breadcrumb" aria-label="Breadcrumb">
				<a href="beranda.php">Beranda</a> / <span><?php echo htmlspecialchars($product['title']); ?></span>
			</nav>

			<!-- Product Detail Grid -->
			<div class="detail-grid">
				<!-- Left: Product Image -->
				<div class="detail-image">
					<img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
				</div>

				<!-- Right: Product Info -->
				<div class="detail-info">
					<h1><?php echo htmlspecialchars($product['title']); ?></h1>

					<!-- Rating -->
					<div class="rating-section">
						<span class="stars"><?php echo render_stars($product['rating']); ?></span>
						<span class="rating-value"><?php echo $product['rating']; ?></span>
						<span class="reviews-count">(<?php echo $product['reviews']; ?> ulasan)</span>
					</div>

					<!-- Price -->
					<div class="price-section">
						<span class="price"><?php echo htmlspecialchars($product['price']); ?></span>
						<span class="stock-status">Stok: <?php echo $product['stock']; ?> tersedia</span>
					</div>

					<!-- Description -->
					<p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

					<!-- Details List -->
					<div class="details-spec">
						<h3>Spesifikasi Produk:</h3>
						<ul>
							<?php foreach ($product['details'] as $detail): ?>
								<li><?php echo htmlspecialchars($detail); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<!-- Quantity & Buy Section -->
					<div class="buy-section">
						<form method="post" action="cart.php?action=add" style="display:flex;align-items:center;gap:12px">
							<div class="quantity-control">
								<label for="qty">Jumlah:</label>
								<div class="qty-input">
									<button type="button" class="qty-btn" id="qty-minus">−</button>
									<input type="number" id="qty" name="qty" value="1" min="1" max="<?php echo $product['stock']; ?>">
									<button type="button" class="qty-btn" id="qty-plus">+</button>
								</div>
							</div>
							<input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
							<button type="submit" class="btn-cart">Tambah ke Keranjang</button>
						</form>
						<button class="btn-buy" type="button" id="buy-now">Beli Sekarang</button>
					</div>
				</div>
			</div>
		</div>
	</main>

	<!-- Footer (sama seperti beranda) -->
	<footer class="site-footer">
		<div class="wrap">
			<div class="footer-col">
				<img src="asset/logo.png" alt="Kerajinan Kelapa" style="width:80px;display:block;margin-bottom:12px">
				<p>Membawa keindahan alam Indonesia ke rumah Anda melalui kerajinan kelapa yang unik dan berkelanjutan.</p>
			</div>
			<div class="footer-col">
				<h4>Hubungi kami</h4>
				<p>Jl. Endik-Byeong KM.18, Endik<br>Email: hello@kerajinankelapa.com<br>Telp: +62 812 3456 7890</p>
			</div>
			<div class="footer-col">
				<h4>Ikuti Kami</h4>
				<p>Facebook · Instagram · YouTube</p>
			</div>
		</div>
		<div class="footer-bottom">© 2025 KerajinanKelapa. Semua Hak Cipta Dilindungi.</div>
	</footer>

	<!-- Quantity control script -->
	<script>
		const qtyInput = document.getElementById('qty');
		const qtyMinus = document.getElementById('qty-minus');
		const qtyPlus = document.getElementById('qty-plus');
		const maxStock = <?php echo $product['stock']; ?>;

		qtyMinus.addEventListener('click', () => {
			let val = parseInt(qtyInput.value) || 1;
			if (val > 1) qtyInput.value = val - 1;
		});

		qtyPlus.addEventListener('click', () => {
			let val = parseInt(qtyInput.value) || 1;
			if (val < maxStock) qtyInput.value = val + 1;
		});

		qtyInput.addEventListener('change', () => {
			let val = parseInt(qtyInput.value) || 1;
			if (val < 1) qtyInput.value = 1;
			if (val > maxStock) qtyInput.value = maxStock;
		});

		// Button actions (buy now will submit the form and then redirect to checkout)
		document.getElementById('buy-now').addEventListener('click', () => {
			// submit the add-to-cart form first, then redirect to checkout
			const form = document.querySelector('form[action^="cart.php"]');
			if (!form) return;
			// create a temporary form to add and then go to checkout
			const temp = document.createElement('form');
			temp.method = 'post';
			temp.action = 'cart.php?action=add';
			const idField = document.createElement('input'); idField.type = 'hidden'; idField.name = 'id'; idField.value = '<?php echo htmlspecialchars($product['id']); ?>'; temp.appendChild(idField);
			const qtyField = document.createElement('input'); qtyField.type = 'hidden'; qtyField.name = 'qty'; qtyField.value = document.getElementById('qty').value || '1'; temp.appendChild(qtyField);
			document.body.appendChild(temp);
			temp.submit();
		});
	</script>
</body>
</html>
