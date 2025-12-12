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

// Handle form submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = trim($_POST['password'] ?? '');
	
	if (empty($email)) {
		$error = 'Email tidak boleh kosong!';
	} elseif (empty($password)) {
		$error = 'Password tidak boleh kosong!';
	} else {
		// Cari user dengan email yang sesuai
		$users = load_users();
		$user_found = null;
		
		foreach ($users as $user) {
			if (strtolower($user['email']) === strtolower($email)) {
				$user_found = $user;
				break;
			}
		}
		
		if (!$user_found) {
			$error = 'Email tidak terdaftar!';
		} elseif (!password_verify($password, $user_found['password'])) {
			$error = 'Password salah!';
		} else {
			// Login berhasil
			$_SESSION['user_id'] = $user_found['id'];
			$_SESSION['user_email'] = $user_found['email'];
			$_SESSION['user_logged_in'] = true;
			
			// Redirect ke beranda pengguna yang sudah login
			header('Location: beranda_loggedin.php');
			exit;
		}
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Masuk - Kerajinan Kelapa</title>
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
					<span class="auth-title">Masuk</span>
				</div>
			</div>
			<a href="beranda.php" class="back-btn" aria-label="Kembali">←</a>
		</div>

		<!-- Form Container -->
		<div class="auth-form-card">
			<?php if ($error): ?>
				<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>

			<!-- Login Form -->
			<h2>Masuk</h2>
			<form method="POST" action="login.php">
				<div class="form-group">
					<label for="email">Masukan NO HP atau G-Email</label>
					<input type="email" id="email" name="email" placeholder="Masukan NO HP atau G-Email" required>
				</div>
				<div class="form-group">
					<label for="password">Masukan Password</label>
					<div class="password-input-wrapper">
						<input type="password" id="password" name="password" placeholder="Masukan Password" required>
						<a href="#" class="forgot-password">Lupa Password</a>
					</div>
				</div>
				<button type="submit" class="btn-submit">Masuk</button>
				<div class="divider">atau</div>
				<div class="social-login">
					<button type="button" class="btn-social facebook">
						<span>f</span> Facebook
					</button>
					<button type="button" class="btn-social google">
						<span>G</span> Google
					</button>
				</div>
				<p class="register-link">Belum Punya akun ? <a href="register.php" class="link-daftar">Daftar</a></p>
			</form>
		</div>

		<!-- Footer -->
		<p class="auth-footer">© 2025 KerajinanKelapa. Semua Hak Cipta Dilindungi.</p>
	</div>

	<style>
		/* Login Page Styles */
		.auth-page {
			background: linear-gradient(135deg, #fcf7f0 0%, #f3efe9 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			font-family: 'Segoe UI', Roboto, Arial, sans-serif;
		}

		.auth-container {
			width: 100%;
			max-width: 700px;
		}

		.auth-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 40px;
			padding-bottom: 20px;
			border-bottom: 1px solid #ddd;
		}

		.logo-brand {
			display: flex;
			align-items: center;
			gap: 15px;
			flex: 1;
		}

		.logo-brand img {
			width: 50px;
			height: 50px;
			border-radius: 4px;
		}

		.brand-name-auth {
			display: block;
			font-weight: 700;
			color: var(--accent);
			font-size: 18px;
			letter-spacing: 0.5px;
		}

		.auth-title {
			display: block;
			font-weight: 700;
			color: #111;
			font-size: 16px;
		}

		.back-btn {
			background: white;
			border: 2px solid var(--border);
			padding: 10px 14px;
			border-radius: 8px;
			text-decoration: none;
			color: #111;
			font-size: 24px;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.back-btn:hover {
			background: var(--accent);
			color: white;
			border-color: var(--accent);
		}

		.auth-form-card {
			background: white;
			padding: 40px;
			border-radius: 16px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
			margin-bottom: 30px;
		}

		.auth-form-card h2 {
			margin: 0 0 30px 0;
			font-size: 28px;
			font-weight: 700;
			color: #111;
		}

		.form-group {
			margin-bottom: 20px;
		}

		.form-group label {
			display: block;
			margin-bottom: 10px;
			font-weight: 600;
			font-size: 14px;
			color: #333;
		}

		.form-group input {
			width: 100%;
			padding: 14px 16px;
			border: 2px solid var(--border);
			border-radius: 10px;
			font-size: 14px;
			box-sizing: border-box;
			transition: all 0.3s ease;
			background: #fafafa;
		}

		.form-group input::placeholder {
			color: #aaa;
		}

		.form-group input:focus {
			outline: none;
			border-color: var(--accent);
			background: white;
			box-shadow: 0 0 0 4px rgba(255, 183, 107, 0.1);
		}

		.password-input-wrapper {
			position: relative;
		}

		.forgot-password {
			position: absolute;
			right: 16px;
			top: 50%;
			transform: translateY(-50%);
			font-size: 13px;
			color: var(--accent);
			text-decoration: none;
			font-weight: 600;
			transition: color 0.3s ease;
		}

		.forgot-password:hover {
			color: #ff9a3f;
			text-decoration: underline;
		}

		.btn-submit {
			width: 100%;
			padding: 16px;
			background: var(--accent);
			color: white;
			border: 0;
			border-radius: 10px;
			font-weight: 700;
			font-size: 16px;
			cursor: pointer;
			transition: all 0.3s ease;
			margin-top: 10px;
		}

		.btn-submit:hover {
			background: #ff9a3f;
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(255, 183, 107, 0.3);
		}

		.btn-submit:active {
			transform: translateY(0);
		}

		.divider {
			text-align: center;
			margin: 28px 0;
			color: #999;
			position: relative;
			font-size: 14px;
		}

		.divider::before,
		.divider::after {
			content: '';
			position: absolute;
			top: 50%;
			width: 44%;
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
			justify-content: center;
		}

		.btn-social {
			flex: 1;
			max-width: 150px;
			padding: 12px 16px;
			border: 2px solid var(--border);
			background: white;
			border-radius: 10px;
			cursor: pointer;
			font-weight: 600;
			font-size: 14px;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.btn-social:hover {
			background: #f9f9f9;
			border-color: #999;
		}

		.btn-social.facebook {
			color: #1877f2;
		}

		.btn-social.google {
			color: #4285f4;
		}

		.btn-social span {
			font-size: 18px;
			font-weight: 700;
		}

		.register-link {
			text-align: center;
			margin-top: 24px;
			font-size: 14px;
			color: #666;
		}

		.link-daftar {
			color: var(--accent);
			text-decoration: none;
			font-weight: 700;
			transition: color 0.3s ease;
		}

		.link-daftar:hover {
			color: #ff9a3f;
			text-decoration: underline;
		}

		.alert {
			padding: 14px 16px;
			border-radius: 10px;
			margin-bottom: 20px;
			font-size: 14px;
			border-left: 4px solid;
		}

		.alert-error {
			background: #fee;
			color: #c33;
			border-left-color: #c33;
		}

		.alert-success {
			background: #efe;
			color: #3c3;
			border-left-color: #3c3;
		}

		.auth-footer {
			text-align: center;
			color: #999;
			font-size: 12px;
			margin-top: 20px;
		}

		/* Responsive Design */
		@media (max-width: 720px) {
			.auth-header {
				margin-bottom: 30px;
			}

			.auth-form-card {
				padding: 30px 20px;
			}

			.auth-form-card h2 {
				font-size: 24px;
				margin-bottom: 24px;
			}

			.form-group {
				margin-bottom: 16px;
			}

			.form-group input {
				padding: 12px 14px;
				font-size: 16px;
			}

			.social-login {
				flex-direction: column;
			}

			.btn-social {
				max-width: 100%;
			}
		}

		@media (max-width: 520px) {
			.logo-brand {
				gap: 10px;
			}

			.brand-name-auth {
				font-size: 16px;
			}

			.auth-title {
				font-size: 14px;
			}

			.back-btn {
				padding: 8px 12px;
				font-size: 20px;
			}

			.auth-form-card {
				padding: 20px 16px;
				border-radius: 12px;
			}

			.auth-form-card h2 {
				font-size: 20px;
				margin-bottom: 20px;
			}

			.form-group label {
				font-size: 12px;
			}

			.btn-submit {
				padding: 14px;
				font-size: 15px;
			}

			.divider {
				margin: 24px 0;
			}

			.register-link {
				margin-top: 20px;
				font-size: 12px;
			}
		}
	</style>
</body>
</html>
