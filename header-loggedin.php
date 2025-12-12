<?php
// Shared header for logged-in pages
// Requires: session started, $user_email and $cart_count, $notif_count variables set
if (session_status() === PHP_SESSION_NONE) session_start();

$user_email = $user_email ?? ($_SESSION['user_email'] ?? '');
$cart_count = $cart_count ?? ($_SESSION['cart_count'] ?? 0);
$notif_count = $notif_count ?? ($_SESSION['notif_count'] ?? 0);

// Nama tampilan dari bagian sebelum @
$user_name = '';
if ($user_email) {
    $parts = explode('@', $user_email);
    $raw = isset($parts[0]) ? $parts[0] : $user_email;
    $user_name = ucwords(str_replace(['.', '_', '-'], ' ', $raw));
}
?>
<header class="site-header">
    <div class="container">
        <div class="logo">
            <img src="asset/logo.png" alt="Kerajinan Kelapa" class="logo-img" width="64" height="64">
        </div>

        <nav class="nav" aria-label="Main navigation">
            <ul>
                <li><a href="beranda_loggedin.php" class="highlight">Beranda</a></li>
                <li><a href="#">Produk</a></li>
                <li><a href="#">Tentang Kami</a></li>
            </ul>
        </nav>

        <div class="actions">
            <div class="nav-item nav-user">
                <a href="profile.php" class="user-pill">
                    <img src="asset/avatar.png" onerror="this.src='asset/logo.png'" alt="Avatar" class="avatar" width="36" height="36">
                    <span class="name"><?php echo htmlspecialchars($user_name ?: 'Pengguna'); ?></span>
                </a>
            </div>
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

                <a href="#" class="icon-link notif" title="Notifikasi">
                    <span class="bell">🔔</span>
                    <?php if ($notif_count > 0): ?>
                        <span class="badge"><?php echo (int)$notif_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</header>
