<?php
session_start();

// Path ke file user data
$users_file = __DIR__ . '/data/users.json';

// Fungsi untuk membaca data user dari JSON
function load_users() {
	global $users_file;
	if (file_exists($users_file)) {
		$json = file_get_contents($users_file);
		return json_decode($json, true) ?? [];
	}
	return [];
}

// Fungsi untuk menyimpan data user ke JSON
function save_users($users) {
	global $users_file;
	file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
}

// Fungsi untuk generate OTP sederhana
function generate_otp() {
	return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Fungsi validasi email
function is_valid_email($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk check apakah email sudah terdaftar
function email_exists($email) {
	$users = load_users();
	foreach ($users as $user) {
		if (strtolower($user['email']) === strtolower($email)) {
			return true;
		}
	}
	return false;
}

// Handle form submission
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// Step 1: Input email/phone
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$password_confirm = $_POST['password_confirm'] ?? '';
	
	if (empty($email)) {
		$error = 'Email tidak boleh kosong!';
	} elseif (!is_valid_email($email)) {
		$error = 'Format email tidak valid!';
	} elseif (email_exists($email)) {
		$error = 'Email sudah terdaftar!';
	} elseif (empty($password)) {
		$error = 'Password tidak boleh kosong!';
	} elseif (strlen($password) < 6) {
		$error = 'Password minimal 6 karakter!';
	} elseif ($password !== $password_confirm) {
		$error = 'Password tidak sesuai!';
	} else {
		// Email dan password valid, simpan ke session dan redirect ke step 2 (success)
		$_SESSION['register_email'] = $email;
		$_SESSION['register_password'] = password_hash($password, PASSWORD_DEFAULT);
		header('Location: register.php?step=2');
		exit;
	}
}

// Step 2: Verifikasi OTP (dihapus - langsung ke selesai)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
	// Langsung simpan dan selesai
	header('Location: register.php?step=3');
	exit;
}

// Step 3: Buat Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
	// Simpan data user
	$users = load_users();
	$new_user = [
		'id' => uniqid(),
		'email' => $_SESSION['register_email'],
		'password' => $_SESSION['register_password'],
		'created_at' => date('Y-m-d H:i:s')
	];
	$users[] = $new_user;
	save_users($users);
	
	// Login otomatis: set session user dan redirect ke beranda_loggedin
	$_SESSION['user_id'] = $new_user['id'];
	$_SESSION['user_email'] = $new_user['email'];
	$_SESSION['user_logged_in'] = true;
	$_n = $_SESSION['user_id'];

	// Clear temporary register session data
	unset($_SESSION['register_email']);
	unset($_SESSION['register_password']);

	// Redirect langsung ke halaman beranda untuk user yang sudah login
	header('Location: beranda_loggedin.php');
	exit;
}

// Validasi step
if (!isset($_SESSION['register_email']) && $step > 1) {
	header('Location: register.php?step=1');
	exit;
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Daftar - Kerajinan Kelapa</title>
	<?php $ver = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
	<link rel="stylesheet" href="css/style.css?v=<?php echo $ver; ?>">
</head>
<body class="auth-page">
	<div class="auth-container">
		<!-- Header dengan logo dan brand -->
		<div class="auth-header">
			<div class="logo-brand">
				<img src="asset/logo.png" alt="Kerajinan Kelapa" width="60" height="60">
				<div>
					<span class="brand-name-auth">KerajinanKelapa</span>
					<span class="auth-title">Daftar</span>
				</div>
			</div>
			<a href="beranda.php" class="back-btn" aria-label="Kembali">←</a>
		</div>

		<!-- Progress Indicator -->
		<div class="progress-steps">
			<div class="step-item <?php echo $step >= 1 ? 'active' : ''; ?>">
				<span class="step-number">1</span>
				<span class="step-label">Verifikasi Email</span>
			</div>
			<div class="step-connector"></div>
			<div class="step-item <?php echo $step >= 2 ? 'active' : ''; ?>">
				<span class="step-number">2</span>
				<span class="step-label">Selesai</span>
			</div>
		</div>

		<!-- Form Container -->
		<div class="auth-form-card">
			<?php if ($error): ?>
				<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>
			<?php if ($success): ?>
				<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
			<?php endif; ?>

			<?php if ($step === 1): ?>
				<!-- Step 1: Daftar -->
				<h2>Daftar</h2>
				<form method="POST" action="register.php?step=1">
					<div class="form-group">
						<label for="email">Masukan Email</label>
						<input type="email" id="email" name="email" placeholder="Masukan Email Anda" required>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" id="password" name="password" placeholder="Masukan Password" required>
					</div>
					<div class="form-group">
						<label for="password_confirm">Konfirmasi Password</label>
						<input type="password" id="password_confirm" name="password_confirm" placeholder="Masukan Ulang Password" required>
					</div>
					<button type="submit" class="btn-submit">Daftar</button>
					<div class="divider">atau</div>
					<div class="social-login">
						<button type="button" class="btn-social facebook">
							<span>f</span> Facebook
						</button>
						<button type="button" class="btn-social google">
							<span>G</span> Google
						</button>
					</div>
					<p class="terms">Dengan mendaftar, saya menyetujui <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a></p>
				</form>

			<?php elseif ($step === 2): ?>
				<!-- Step 2: Selesai -->
				<div class="success-message">
					<div class="success-icon">✓</div>
					<h2>Selesai</h2>
					<p>Pendaftaran Berhasil</p>
				</div>
				<form method="POST" action="register.php?step=3">
					<button type="submit" class="btn-submit">Masuk Sekarang</button>
				</form>

			<?php endif; ?>
		</div>

		<!-- Footer -->
		<p class="auth-footer">© 2025 KerajinanKelapa. Semua Hak Cipta Dilindungi.</p>
	</div>

	<style>
		/* Auth Page Styles (temporary inline, akan dipindah ke style.css) */
		.auth-page {
			background: linear-gradient(135deg, #fcf7f0 0%, #f3efe9 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
		}

		.auth-container {
			width: 100%;
			max-width: 600px;
		}

		.auth-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30px;
			text-align: center;
		}

		.logo-brand {
			display: flex;
			align-items: center;
			gap: 12px;
			flex: 1;
		}

		.logo-brand img {
			width: 50px;
			height: 50px;
		}

		.brand-name-auth {
			display: block;
			font-weight: 700;
			color: var(--accent);
			font-size: 18px;
		}

		.auth-title {
			display: block;
			font-weight: 700;
			color: #111;
			font-size: 16px;
		}

		.back-btn {
			background: white;
			border: 1px solid var(--border);
			padding: 8px 12px;
			border-radius: 6px;
			text-decoration: none;
			color: #111;
			font-size: 20px;
			cursor: pointer;
		}

		.progress-steps {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 40px;
		}

		.step-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			flex: 1;
		}

		.step-number {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background: white;
			border: 2px solid #ddd;
			font-weight: 700;
			color: #999;
		}

		.step-item.active .step-number {
			background: var(--accent);
			border-color: var(--accent);
			color: white;
		}

		.step-label {
			font-size: 12px;
			color: #999;
			text-align: center;
		}

		.step-item.active .step-label {
			color: var(--accent);
			font-weight: 600;
		}

		.step-connector {
			flex: 1;
			height: 2px;
			background: #ddd;
			margin: 0 10px;
		}

		.auth-form-card {
			background: white;
			padding: 30px;
			border-radius: 12px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
			margin-bottom: 20px;
		}

		.auth-form-card h2 {
			margin: 0 0 20px 0;
			font-size: 24px;
		}

		.form-group {
			margin-bottom: 16px;
		}

		.form-group label {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
			font-size: 14px;
		}

		.form-group input {
			width: 100%;
			padding: 12px;
			border: 1px solid var(--border);
			border-radius: 8px;
			font-size: 14px;
			box-sizing: border-box;
		}

		.form-group input:focus {
			outline: none;
			border-color: var(--accent);
			box-shadow: 0 0 0 3px rgba(255, 183, 107, 0.1);
		}

		.btn-submit {
			width: 100%;
			padding: 14px;
			background: var(--accent);
			color: white;
			border: 0;
			border-radius: 8px;
			font-weight: 700;
			font-size: 16px;
			cursor: pointer;
		}

		.btn-submit:hover {
			background: #ff9a3f;
		}

		.divider {
			text-align: center;
			margin: 24px 0;
			color: #999;
			position: relative;
		}

		.divider::before,
		.divider::after {
			content: '';
			position: absolute;
			top: 50%;
			width: 45%;
			height: 1px;
			background: #ddd;
		}

		.divider::before {
			left: 0;
		}

		.divider::after {
			right: 0;
		}

		.social-login {
			display: flex;
			gap: 12px;
		}

		.btn-social {
			flex: 1;
			padding: 12px;
			border: 1px solid var(--border);
			background: white;
			border-radius: 8px;
			cursor: pointer;
			font-weight: 600;
			font-size: 14px;
		}

		.btn-social.facebook {
			color: #1877f2;
		}

		.btn-social.google {
			color: #4285f4;
		}

		.terms {
			font-size: 12px;
			color: #999;
			text-align: center;
			margin-top: 16px;
		}

		.terms a {
			color: var(--accent);
			text-decoration: none;
		}

		.otp-info,
		.password-info {
			color: #666;
			margin-bottom: 20px;
			font-size: 14px;
		}

		.otp-timer {
			text-align: center;
			color: #999;
			font-size: 12px;
			margin: 12px 0;
		}

		.success-message {
			text-align: center;
			padding: 30px 0;
		}

		.success-icon {
			width: 80px;
			height: 80px;
			margin: 0 auto 20px;
			background: var(--accent);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 48px;
			color: white;
		}

		.alert {
			padding: 12px;
			border-radius: 6px;
			margin-bottom: 16px;
			font-size: 14px;
		}

		.alert-error {
			background: #fee;
			color: #c33;
			border: 1px solid #fcc;
		}

		.alert-success {
			background: #efe;
			color: #3c3;
			border: 1px solid #cfc;
		}

		.auth-footer {
			text-align: center;
			color: #999;
			font-size: 12px;
		}

		@media (max-width: 600px) {
			.auth-form-card {
				padding: 20px;
			}

			.progress-steps {
				margin-bottom: 30px;
			}

			.step-label {
				font-size: 10px;
			}
		}
	</style>
</body>
</html>
