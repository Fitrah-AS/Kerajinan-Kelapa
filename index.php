<?php
// Include data produk terpusat
require_once 'products-data.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$cart_count = $_SESSION['cart_count'] ?? 0;
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Beranda - Kerajinan Kelapa</title>
	<?php $ver = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
	<link rel="stylesheet" href="css/style.css?v=<?php echo $ver; ?>">
</head>
<body>
	<header class="site-header">
		<div class="container">
			<div class="logo">
				<!-- logo image (replaced inline SVG) -->
				<img src="asset/logo.png" alt="Kerajinan Kelapa" class="logo-img" width="64" height="64">
			</div>

			<nav class="nav" aria-label="Main navigation">
				<ul>
					<li><a href="#" class="highlight">Beranda</a></li>
					<li><a href="#">Produk</a></li>
					<li><a href="#">Tentang Kami</a></li>
				</ul>
			</nav>

			<div class="actions">
				<a href="register.php" class="btn">Daftar</a>
				<a href="login.php" class="btn login">Login</a>
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

				
			</div>
		</div>
	</header>

	<!-- Carousel / slider -->
	<section class="carousel" aria-label="Promosi">
		<div class="carousel-inner">
			<div class="slides">
				<div class="slide"><img src="figma/banner1.svg" alt="Promo 1"></div>
				<div class="slide"><img src="figma/banner2.svg" alt="Promo 2"></div>
				<div class="slide"><img src="figma/banner3.svg" alt="Promo 3"></div>
			</div>
			<button class="arrow prev" aria-label="Sebelumnya">◀</button>
			<button class="arrow next" aria-label="Berikutnya">▶</button>
			<div class="dots" role="tablist"></div>
		</div>
	</section>


	<!-- Products (rendered from PHP array) -->
	<section class="products" aria-label="Produk Populer">
		<div class="heading">
			<h3>Produk Populer</h3>
			<small>Produk terbaik dari pengrajin lokal</small>
		</div>
		<div class="product-grid">
			<?php foreach($all_products as $p): ?>
				<div class="card-link">
					<article class="card">
						<a href="product-detail.php?id=<?php echo $p['id']; ?>" style="text-decoration:none;color:inherit;display:block">
							<img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
							<div class="title"><?php echo htmlspecialchars($p['title']); ?></div>
							<div class="price"><?php echo htmlspecialchars($p['price']); ?></div>
						</a>
					<div style="margin-top:10px;display:flex;justify-content:center">
						<a href="<?php echo empty($_SESSION['user_logged_in']) ? 'login.php' : 'cart.php?action=add&id=' . $p['id']; ?>" class="btn btn-add" style="padding:8px 12px;border-radius:8px">Tambah</a>
					</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
		<a class="see-more" href="#">Masuk Untuk Lihat Lainnya</a>
	</section>

	<!-- Footer -->
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

	<!-- slider script -->
	<script src="js/slider.js"></script>
</body>
</html>
