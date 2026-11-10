<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/products-data.php';

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function storage_path(string $name): string
{
    $allowed = ['users.json', 'carts.json', 'orders.json'];
    if (!in_array($name, $allowed, true)) {
        throw new InvalidArgumentException('Nama penyimpanan tidak diizinkan.');
    }

    $directory = getenv('RUANG_KELAPA_DATA_DIR');
    if ($directory === false || trim($directory) === '') {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ruang-kelapa-' . substr(hash('sha256', __DIR__), 0, 16);
    }
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder data tidak dapat dibuat.');
    }

    return $directory . DIRECTORY_SEPARATOR . $name;
}

function store_read(string $name): array
{
    $path = storage_path($name);
    if (!is_file($path)) {
        return [];
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Data tidak dapat dibuka.');
    }

    try {
        if (!flock($handle, LOCK_SH)) {
            throw new RuntimeException('Data tidak dapat dikunci untuk dibaca.');
        }
        $contents = stream_get_contents($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }

    if ($contents === false || trim($contents) === '') {
        return [];
    }

    $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new UnexpectedValueException('Format data penyimpanan tidak valid.');
    }

    return $data;
}

function store_write(string $name, array $data): void
{
    $handle = fopen(storage_path($name), 'c+b');
    if ($handle === false) {
        throw new RuntimeException('Data tidak dapat disimpan.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Data tidak dapat dikunci untuk disimpan.');
        }
        $contents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, $contents) !== strlen($contents) || !fflush($handle)) {
            throw new RuntimeException('Penulisan data tidak selesai.');
        }
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): array
{
    return [
        'id' => (string)($_SESSION['user_id'] ?? ''),
        'email' => (string)($_SESSION['user_email'] ?? ''),
        'name' => (string)($_SESSION['user_name'] ?? ''),
    ];
}

function require_login(): void
{
    if (!is_logged_in()) {
        $page = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
        $_SESSION['after_login'] = in_array($page, ['checkout.php', 'orders.php', 'profile.php'], true) ? $page : 'index.php';
        $_SESSION['flash'] = 'Masuk atau buat akun untuk melanjutkan.';
        redirect('login.php');
    }
}

function flash(string $message = ''): string
{
    if ($message !== '') {
        $_SESSION['flash'] = $message;
        return '';
    }

    $current = (string)($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $current;
}

function cart_contents(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
        if (is_logged_in()) {
            $carts = store_read('carts.json');
            $saved = $carts[current_user()['id']] ?? $carts[current_user()['email']] ?? [];
            foreach ($saved as $key => $item) {
                if (is_array($item) && isset($item['id'], $item['qty'])) {
                    $_SESSION['cart'][(int)$item['id']] = max(0, (int)$item['qty']);
                } elseif (is_numeric($key)) {
                    $_SESSION['cart'][(int)$key] = max(0, (int)$item);
                }
            }
        }
    }

    return $_SESSION['cart'];
}

function save_cart(): void
{
    $cart = cart_contents();
    $_SESSION['cart_count'] = array_sum($cart);
    if (!is_logged_in()) {
        return;
    }

    $carts = store_read('carts.json');
    $carts[current_user()['id']] = $cart;
    store_write('carts.json', $carts);
}

function cart_count(): int
{
    return array_sum(cart_contents());
}

function product_price(array $product): int
{
    return (int)($product['price_value'] ?? filter_var($product['price'], FILTER_SANITIZE_NUMBER_INT));
}

function rupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function product_categories(): array
{
    return [
        'Semua' => 'Semua',
        'Dekorasi' => 'Dekorasi',
        'Peralatan makan' => 'Peralatan makan',
        'Penerangan' => 'Penerangan',
        'Tanaman' => 'Tanaman',
    ];
}

function render_header(string $active = ''): void
{
    $user = current_user();
    ?>
    <header class="site-header">
        <div class="topbar wrap">
            <a class="brand" href="index.php" aria-label="Kerajinan Kelapa - Beranda">
                <span class="brand-mark">K</span>
                <span><strong>Ruang Kelapa</strong><small>Kerajinan lokal, cerita alam</small></span>
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a class="<?= $active === 'home' ? 'active' : '' ?>" href="index.php">Beranda</a>
                <a href="index.php#produk">Produk</a>
                <a href="index.php#tentang">Tentang</a>
            </nav>
            <div class="header-actions">
                <a class="cart-link" href="cart.php" aria-label="Keranjang, <?= cart_count() ?> barang">Keranjang <span><?= cart_count() ?></span></a>
                <?php if (is_logged_in()): ?>
                    <a class="user-link" href="profile.php"><?= e($user['name'] ?: $user['email']) ?></a>
                    <a class="text-link" href="orders.php">Pesanan</a>
                <?php else: ?>
                    <a class="text-link" href="login.php">Masuk</a>
                    <a class="button button-small" href="register.php">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php
}

function render_footer(): void
{
    ?>
    <footer class="site-footer" id="tentang">
        <div class="wrap footer-grid">
            <div><a class="brand footer-brand" href="index.php"><span class="brand-mark">K</span><span><strong>Ruang Kelapa</strong><small>Kerajinan lokal, cerita alam</small></span></a><p>Produk kerajinan berbahan kelapa, dibuat dengan teliti oleh tangan pengrajin lokal.</p></div>
            <div><h3>Jelajahi</h3><a href="index.php#produk">Katalog produk</a><a href="orders.php">Pesanan saya</a></div>
            <div><h3>Bantuan</h3><p>Perlu bantuan? Hubungi kami di<br><a href="mailto:halo@ruangkelapa.id">halo@ruangkelapa.id</a></p><p>Senin–Sabtu, 09.00–17.00 WIB</p></div>
        </div>
        <div class="footer-bottom">© <?= date('Y') ?> Ruang Kelapa · Dibuat dengan bangga di Indonesia</div>
    </footer>
    <?php
}
