<?php
require("config/config.default.php");
require("config/config.candy.php");

$logo_src = !empty($setting['logo']) ? "$homeurl/{$setting['logo']}" : "$homeurl/dist/img/tutwuri.jpg";
$bc_src = !empty($setting['bc']) ? "$homeurl/{$setting['bc']}" : "$homeurl/dist/img/bc.jpg";
?>
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login | <?= htmlspecialchars($setting['aplikasi']) ?></title>
	<link rel="icon" type="image/png" href="favicon.ico" />
	<!-- Local Offline Assets -->
	<link rel="stylesheet" href="<?= $homeurl ?>/dist/css/tailwind.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.css">
	<style>
		.cbt-login-card {
			display: flex;
			flex-direction: row;
			width: 100%;
			max-width: 960px;
			background-color: #ffffff;
			border-radius: 2.25rem;
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.03);
			overflow: hidden;
			min-height: 540px;
			position: relative;
			z-index: 10;
		}
		.cbt-left-pane {
			width: 48%;
			position: relative;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			padding: 2.5rem;
			background-color: #f8fafc;
			flex-shrink: 0;
		}
		.cbt-left-img {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
			z-index: 1;
		}
		.cbt-left-fade {
			position: absolute;
			inset: 0;
			background: linear-gradient(to right, rgba(255, 255, 255, 0) 15%, rgba(255, 255, 255, 0.4) 55%, rgba(255, 255, 255, 1) 100%);
			z-index: 2;
			pointer-events: none;
		}
		.cbt-right-pane {
			width: 52%;
			position: relative;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			padding: 2.5rem 3rem;
			background-color: #ffffff;
			z-index: 5;
			flex-grow: 1;
		}
		.cbt-pill-input {
			width: 100%;
			padding: 0.85rem 1.35rem;
			border-radius: 9999px;
			background-color: #f1f5f9;
			color: #1e293b;
			font-size: 0.875rem;
			border: 1px solid transparent;
			outline: none;
			transition: all 0.2s ease-in-out;
		}
		.cbt-pill-input:focus {
			background-color: #ffffff;
			border-color: #007a4d;
			box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.15);
		}
		.cbt-pill-btn {
			width: 100%;
			padding: 0.85rem 1.5rem;
			border-radius: 9999px;
			background-color: #007a4d;
			color: #ffffff;
			font-weight: 600;
			font-size: 0.875rem;
			box-shadow: 0 4px 14px rgba(0, 122, 77, 0.28);
			border: none;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
			transition: all 0.2s ease-in-out;
		}
		.cbt-pill-btn:hover {
			background-color: #006640;
		}
		.cbt-pill-btn:active {
			transform: scale(0.98);
		}
		@media (max-width: 860px) {
			.cbt-login-card {
				flex-direction: column;
				max-width: 460px;
			}
			.cbt-left-pane {
				width: 100%;
				min-height: 220px;
				padding: 1.5rem;
			}
			.cbt-left-fade {
				background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 10%, rgba(255, 255, 255, 0.5) 60%, rgba(255, 255, 255, 1) 100%);
			}
			.cbt-right-pane {
				width: 100%;
				padding: 2rem 1.5rem;
			}
		}
	</style>
</head>

<body class="min-h-screen bg-slate-100/75 font-sans text-slate-800 antialiased relative flex items-center justify-center p-3 sm:p-6 lg:p-8 selection:bg-emerald-500 selection:text-white">

	<!-- Outer Card (Clean White, Rounded-3xl, Elevated Shadow) -->
	<main class="cbt-login-card my-auto">
		
		<!-- Left Panel: Uploaded Background with Seamless Fade-to-White (48% width) -->
		<div class="cbt-left-pane">
			
			<!-- Background Image from Admin Upload -->
			<img src="<?= $bc_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/bc.jpg';" alt="Background" class="cbt-left-img">
			
			<!-- Seamless Gradient Blend into the white canvas -->
			<div class="cbt-left-fade"></div>

			<!-- Top-Left Icon / Logo -->
			<div style="position: relative; z-index: 10;">
				<div style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); border-radius: 1rem; padding: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid rgba(226,232,240,0.8); overflow: hidden;">
					<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 32px; max-height: 32px; width: 100%; height: 100%; object-fit: contain;">
				</div>
			</div>

			<!-- Bottom-Left School Branding (Clean, Real Info, Matching Reference) -->
			<div style="position: relative; z-index: 10; margin-top: auto; padding-top: 1.5rem; text-align: left;">
				<h1 style="font-size: 1.75rem; line-height: 1.15; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0;">
					<?= htmlspecialchars($setting['sekolah']) ?>
				</h1>
				<p style="font-size: 1.1rem; font-family: Georgia, serif; font-style: italic; color: #007a4d; font-weight: 600; margin-top: 0.25rem; margin-bottom: 0;">
					<?= htmlspecialchars($setting['aplikasi']) ?>
				</p>
				<p style="font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 0.5rem; margin-bottom: 0;">
					<?= htmlspecialchars("$setting[kecamatan] - $setting[kota]") ?>
				</p>
			</div>

		</div>

		<!-- Right Panel: Centered Clean Login Form (52% width) -->
		<div class="cbt-right-pane">
			
			<!-- Top Spacer for vertical balance -->
			<div></div>

			<!-- Centered Form Container -->
			<div style="width: 100%; max-width: 340px; margin: auto; text-align: center; padding: 1rem 0;">
				
				<!-- Center Logo Icon (Matches user reference icon) -->
				<div style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; border-radius: 0.875rem; background-color: #f1f5f9; padding: 6px; border: 1px solid #e2e8f0; overflow: hidden;">
					<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 30px; max-height: 30px; width: 100%; height: 100%; object-fit: contain;">
				</div>

				<!-- Heading (Matching reference) -->
				<h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0;">
					Welcome Back!
				</h2>
				<p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem; margin-bottom: 1.5rem;">
					Login to Your Account
				</p>

				<!-- Form Inputs -->
				<form id="formlogin" action="ceklogin.php" method="POST" style="display: flex; flex-direction: column; gap: 0.875rem; text-align: left;" autocomplete="off">
					
					<!-- Username Field (Pill Rounded) -->
					<div style="position: relative;">
						<input type="text" id="username" name="username" required autocomplete="username" placeholder="eg. username / no. peserta"
							class="cbt-pill-input">
					</div>

					<!-- Password Field (Pill Rounded with eye toggle) -->
					<div style="position: relative;">
						<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="password"
							class="cbt-pill-input" style="padding-right: 2.75rem;">
						
						<!-- Toggle Password Eye -->
						<button type="button" id="btnShowPass" tabindex="-1" style="position: absolute; top: 0; bottom: 0; right: 0; padding-right: 1rem; display: flex; align-items: center; background: none; border: none; color: #94a3b8; cursor: pointer; outline: none;" title="Lihat Password">
							<svg id="eyeOpen" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
							</svg>
							<svg id="eyeClosed" style="width: 16px; height: 16px; display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
							</svg>
						</button>
					</div>

					<!-- Sign In Button (Pill Rounded Emerald #007a4d) -->
					<div style="padding-top: 0.35rem;">
						<button type="submit" id="btnSubmit" class="cbt-pill-btn">
							<span id="btnText">Sign In</span>
							<svg id="btnSpinner" style="width: 16px; height: 16px; display: none; animation: spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
								<circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</button>
					</div>
				</form>
			</div>

			<!-- Bottom Right Minimal Info -->
			<div style="padding-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; font-size: 0.75rem; color: #94a3b8; font-weight: 500;">
				<span>Versi <?= htmlspecialchars(VERSI . " r" . REVISI) ?></span>
				<span>&bull;</span>
				<span>&copy; <?= date('Y') ?> <?= htmlspecialchars(APLIKASI) ?></span>
			</div>

		</div>

	</main>

	<!-- Local Offline Scripts -->
	<script src="<?= $homeurl ?>/dist/vendor/jquery/jquery-3.2.1.min.js"></script>
	<script src="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.js"></script>
	<script>
		$(document).ready(function() {
			// Password visibility toggle
			$('#btnShowPass').click(function(e) {
				e.preventDefault();
				var passInput = $('#password');
				var isPassword = passInput.attr('type') === 'password';
				passInput.attr('type', isPassword ? 'text' : 'password');
				if (isPassword) {
					$('#eyeOpen').hide();
					$('#eyeClosed').show();
				} else {
					$('#eyeOpen').show();
					$('#eyeClosed').hide();
				}
			});

			// Form login AJAX
			$('#formlogin').submit(function(e) {
				e.preventDefault();
				var form = $(this);
				var homeurl = '<?= $homeurl ?>';
				var btn = $('#btnSubmit');
				var btnText = $('#btnText');
				var btnSpinner = $('#btnSpinner');

				btn.prop('disabled', true);
				btnText.text('Signing in...');
				btnSpinner.show();

				$.ajax({
					type: 'POST',
					url: form.attr('action'),
					data: form.serialize(),
					success: function(data) {
						data = (data || '').trim();

						if (data === "ok") {
							btnText.text('Success!');
							swal({
								title: 'Login Berhasil',
								text: 'Mengalihkan...',
								type: 'success',
								timer: 1200,
								showConfirmButton: false
							}).then(function() {
								window.location = homeurl;
							});
							setTimeout(function() {
								window.location = homeurl;
							}, 1300);
						} else {
							btn.prop('disabled', false);
							btnText.text('Sign In');
							btnSpinner.hide();

							if (data === "nopass") {
								swal({
									type: 'warning',
									title: 'Password Salah',
									text: 'Periksa kembali password akun Anda.',
									confirmButtonColor: '#007a4d'
								});
							} else if (data === "td") {
								swal({
									type: 'error',
									title: 'Tidak Terdaftar',
									text: 'Username tidak ditemukan dalam sistem.',
									confirmButtonColor: '#007a4d'
								});
							} else if (data === "nologin") {
								swal({
									type: 'warning',
									title: 'Akun Sedang Aktif',
									text: 'Akun ini sedang aktif di perangkat lain.',
									confirmButtonColor: '#007a4d'
								});
							} else if (data === "ta") {
								swal({
									type: 'warning',
									title: 'Belum Aktif',
									text: 'Silahkan hubungi panitia untuk aktivasi akun.',
									confirmButtonColor: '#007a4d'
								});
							} else {
								swal({
									type: 'error',
									title: 'Gagal Masuk',
									text: 'Terjadi kesalahan (' + data + ').',
									confirmButtonColor: '#007a4d'
								});
							}
						}
					},
					error: function() {
						btn.prop('disabled', false);
						btnText.text('Sign In');
						btnSpinner.hide();

						swal({
							type: 'error',
							title: 'Gangguan Jaringan',
							text: 'Tidak dapat terhubung ke server.',
							confirmButtonColor: '#007a4d'
						});
					}
				});
				return false;
			});
		});
	</script>
</body>

</html>