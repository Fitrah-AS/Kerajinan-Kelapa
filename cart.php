<?php
// Halaman Keranjang: menyimpan data keranjang per user di data/carts.json
require_once 'products-data.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Pastikan user sudah login
if (empty($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

$user_email = $_SESSION['user_email'] ?? '';
$cart_file = __DIR__ . '/data/carts.json';

function load_carts($path) {
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_carts($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$carts = load_carts($cart_file);
$user_cart = $carts[$user_email] ?? [];

// Helper: find product by id
function find_product($products, $id) {
    foreach ($products as $p) {
        if ((string)$p['id'] === (string)$id) return $p;
    }
    return null;
}

// Actions: add, remove, update
$action = $_REQUEST['action'] ?? '';
$id = $_REQUEST['id'] ?? null;

if ($action === 'add' && $id) {
    // add qty (accept qty via request, default 1)
    $qtyToAdd = isset($_REQUEST['qty']) ? max(1, (int)$_REQUEST['qty']) : 1;
    $found = false;
    foreach ($user_cart as &$it) {
        if ((string)$it['id'] === (string)$id) { $it['qty'] = (int)$it['qty'] + $qtyToAdd; $found = true; break; }
    }
    unset($it);
    if (!$found) {
        $user_cart[] = ['id' => $id, 'qty' => $qtyToAdd];
    }
    $carts[$user_email] = $user_cart;
    save_carts($cart_file, $carts);
    // update session cart count
    $_SESSION['cart_count'] = array_sum(array_column($user_cart, 'qty'));
    header('Location: cart.php'); exit;
}

if ($action === 'remove' && $id) {
    foreach ($user_cart as $k => $it) {
        if ((string)$it['id'] === (string)$id) { unset($user_cart[$k]); }
    }
    $user_cart = array_values($user_cart);
    $carts[$user_email] = $user_cart;
    save_carts($cart_file, $carts);
    $_SESSION['cart_count'] = array_sum(array_column($user_cart, 'qty'));
    header('Location: cart.php'); exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    if ($id) {
        foreach ($user_cart as $k => $it) {
            if ((string)$it['id'] === (string)$id) {
                if ($qty > 0) $user_cart[$k]['qty'] = $qty; else unset($user_cart[$k]);
            }
        }
        $user_cart = array_values($user_cart);
        $carts[$user_email] = $user_cart;
        save_carts($cart_file, $carts);
        $_SESSION['cart_count'] = array_sum(array_column($user_cart, 'qty'));
    }
    header('Location: cart.php'); exit;
}

// Render page
function rupiah($n){ return 'Rp. ' . number_format($n,0,',','.'); }

$items = [];
$subtotal = 0;
foreach ($user_cart as $it) {
    $prod = find_product($all_products, $it['id']);
    if (!$prod) continue;
    $qty = (int)$it['qty'];
    $price_num = (int)filter_var($prod['price'], FILTER_SANITIZE_NUMBER_INT);
    $line_total = $price_num * $qty;
    $subtotal += $line_total;
    $items[] = ['product' => $prod, 'qty' => $qty, 'line_total' => $line_total];
}

$shipping = 20000;
$total = $subtotal + ($subtotal>0 ? $shipping : 0);

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Keranjang Belanja</title>
    <?php $ver = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $ver; ?>">
</head>
<body>
    <?php include 'header-loggedin.php'; ?>

    <main class="main-content">
        <h1 style="text-align:center;font-size:32px;margin:24px 0 20px;color:#111">Keranjang Belanja</h1>

        <?php if (empty($items)): ?>
            <p style="text-align:center;color:#999;margin:40px 0">Keranjang Anda kosong. <a href="beranda_loggedin.php" style="color:#ffb76b;font-weight:600">Kembali berbelanja</a></p>
        <?php else: ?>
            <div style="max-width:1000px;margin:0 auto;padding:0 20px">
                <!-- Cart Items Section -->
                <div style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,0.04);margin-bottom:28px">
                    <?php foreach($items as $row): $p = $row['product']; ?>
                        <div style="display:flex;align-items:center;gap:16px;padding:18px 20px;border-bottom:1px solid #f0f0f0;transition:background 0.2s">
                            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>" style="width:100px;height:100px;object-fit:cover;border-radius:10px;flex-shrink:0">
                            <div style="flex:1">
                                <div style="font-size:18px;font-weight:700;color:#111;margin-bottom:6px"><?php echo htmlspecialchars($p['title']); ?></div>
                                <div style="color:#999;font-size:14px"><?php echo rupiah((int)filter_var($p['price'], FILTER_SANITIZE_NUMBER_INT)); ?> per item</div>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;min-width:180px">
                                <form method="post" action="cart.php?action=update" style="display:flex;gap:8px;align-items:center">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                                    <input type="number" name="qty" value="<?php echo $row['qty']; ?>" min="1" style="width:50px;padding:8px;border:1px solid #ddd;border-radius:6px;text-align:center;font-weight:600">
                                    <button type="submit" class="btn" style="padding:8px 12px;font-size:12px;background:#f0f0f0;border:0;border-radius:6px;color:#111;cursor:pointer;font-weight:600;transition:background 0.2s">Update</button>
                                </form>
                            </div>
                            <div style="min-width:120px;text-align:right;font-weight:700;font-size:16px;color:#ffb76b"><?php echo rupiah($row['line_total']); ?></div>
                            <div style="width:44px">
                                <a href="cart.php?action=remove&id=<?php echo htmlspecialchars($p['id']); ?>" class="btn" style="background:#fff;border:1px solid #ff6b6b;color:#ff6b6b;border-radius:8px;padding:8px;cursor:pointer;font-weight:600;text-align:center;text-decoration:none;display:block;transition:all 0.2s" title="Hapus">🗑</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary and Buttons -->
                <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start">
                    <div></div>
                    <div style="background:#fff8f0;border-radius:12px;padding:20px;border:1px solid #ffe8d1">
                        <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:14px;color:#666">
                            <span>Subtotal</span>
                            <span><?php echo rupiah($subtotal); ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #ffe8d1;font-size:14px;color:#666">
                            <span>Biaya Pengiriman</span>
                            <span><?php echo rupiah($shipping); ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:#111">
                            <span>Total</span>
                            <span style="color:#ffb76b"><?php echo rupiah($total); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display:flex;justify-content:center;gap:16px;margin-top:28px;margin-bottom:40px">
                    <a href="beranda_loggedin.php" class="btn" style="padding:14px 28px;background:#fff;border:1px solid #e0e0e0;border-radius:10px;text-decoration:none;color:#111;font-weight:700;cursor:pointer;transition:all 0.2s">← Lanjut Belanja</a>
                    <a href="checkout.php" class="btn" style="padding:14px 28px;background:#ffb76b;border:0;border-radius:10px;text-decoration:none;color:#fff;font-weight:700;cursor:pointer;transition:background 0.2s">Selesaikan Pembayaran →</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
