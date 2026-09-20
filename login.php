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
		.fade-to-right {
			background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.45) 60%, rgba(255, 255, 255, 1) 100%);
		}
		@media (max-width: 1023px) {
			.fade-to-right {
				background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.5) 60%, rgba(255, 255, 255, 1) 100%);
			}
		}
	</style>
</head>

<body class="min-h-screen bg-slate-100/70 font-sans text-slate-800 antialiased relative flex items-center justify-center p-3 sm:p-6 lg:p-8 selection:bg-emerald-500 selection:text-white">

	<!-- Outer Card (Clean White, Rounded-3xl, Elevated Shadow) -->
	<main class="relative z-10 w-full max-w-4xl my-auto bg-white rounded-3xl sm:rounded-[2.5rem] shadow-2xl shadow-slate-300/60 border border-slate-100 overflow-hidden flex flex-col lg:flex-row min-h-[520px]">
		
		<!-- Left Panel: Uploaded Background with Seamless Fade-to-White (5/12 on LG) -->
		<div class="relative w-full lg:w-5/12 bg-slate-50 p-6 sm:p-8 flex flex-col justify-between overflow-hidden min-h-[220px] lg:min-h-[520px] shrink-0">
			
			<!-- Background Image from Admin Upload -->
			<img src="<?= $bc_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/bc.jpg';" alt="Background" class="absolute inset-0 w-full h-full object-cover">
			
			<!-- Seamless Gradient Blend into the white canvas -->
			<div class="absolute inset-0 fade-to-right pointer-events-none"></div>

			<!-- Top-Left Icon / Logo -->
			<div class="relative z-10">
				<div class="inline-flex items-center justify-center rounded-2xl bg-white/90 p-1.5 shadow-sm ring-1 ring-slate-200/60 backdrop-blur-md overflow-hidden" style="width: 44px; height: 44px;">
					<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 32px; max-height: 32px; width: 100%; height: 100%; object-fit: contain;">
				</div>
			</div>

			<!-- Bottom-Left School Branding (Clean, Real Info, No AI fluff) -->
			<div class="relative z-10 mt-auto pt-6 text-left">
				<h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
					<?= htmlspecialchars($setting['sekolah']) ?>
				</h1>
				<p class="text-base sm:text-lg font-serif italic text-emerald-700 font-semibold mt-1">
					<?= htmlspecialchars($setting['aplikasi']) ?>
				</p>
				<p class="text-xs text-slate-600 mt-2 font-medium">
					<?= htmlspecialchars("$setting[kecamatan] - $setting[kota]") ?>
				</p>
			</div>

		</div>

		<!-- Right Panel: Centered Clean Login Form (7/12 on LG) -->
		<div class="w-full lg:w-7/12 p-6 sm:p-8 lg:p-12 flex flex-col justify-between bg-white relative grow">
			
			<!-- Spacer for top balance -->
			<div class="hidden lg:block"></div>

			<!-- Centered Form Container -->
			<div class="w-full max-w-sm mx-auto my-auto text-center py-4">
				
				<!-- Center Logo Icon -->
				<div class="inline-flex items-center justify-center mx-auto mb-3.5 rounded-2xl bg-slate-100 p-2 ring-1 ring-slate-200/80 shadow-xs overflow-hidden" style="width: 46px; height: 46px;">
					<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 32px; max-height: 32px; width: 100%; height: 100%; object-fit: contain;">
				</div>

				<!-- Heading -->
				<h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
					Welcome Back!
				</h2>
				<p class="text-xs text-slate-400 mt-1 mb-6">
					Login to Your Account
				</p>

				<!-- Form Inputs -->
				<form id="formlogin" action="ceklogin.php" method="POST" class="space-y-3.5 text-left" autocomplete="off">
					
					<!-- Username Field (Pill Rounded) -->
					<div class="relative">
						<input type="text" id="username" name="username" required autocomplete="username" placeholder="eg. username / no. peserta"
							class="w-full px-5 py-3.5 rounded-full text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white border border-transparent focus:border-emerald-500/30 transition-all"
							style="background-color: #f1f5f9;">
					</div>

					<!-- Password Field (Pill Rounded with eye toggle) -->
					<div class="relative">
						<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="password"
							class="w-full pl-5 pr-12 py-3.5 rounded-full text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white border border-transparent focus:border-emerald-500/30 transition-all"
							style="background-color: #f1f5f9;">
						
						<!-- Toggle Password Eye -->
						<button type="button" id="btnShowPass" tabindex="-1" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none" title="Lihat Password">
							<svg id="eyeOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
							</svg>
							<svg id="eyeClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
							</svg>
						</button>
					</div>

					<!-- Sign In Button (Pill Rounded Emerald) -->
					<div class="pt-2">
						<button type="submit" id="btnSubmit" class="w-full py-3.5 px-6 rounded-full text-white font-semibold text-sm shadow-md shadow-emerald-600/25 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
							style="background-color: #059669;">
							<span id="btnText">Sign In</span>
							<svg id="btnSpinner" class="w-4 h-4 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</button>
					</div>
				</form>
			</div>

			<!-- Bottom Right Minimal Info -->
			<div class="pt-4 flex items-center justify-end gap-3 text-[11px] text-slate-400 font-medium">
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
				$('#eyeOpen').toggleClass('hidden', isPassword);
				$('#eyeClosed').toggleClass('hidden', !isPassword);
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
				btnSpinner.removeClass('hidden');

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
							btnSpinner.addClass('hidden');

							if (data === "nopass") {
								swal({
									type: 'warning',
									title: 'Password Salah',
									text: 'Periksa kembali password akun Anda.',
									confirmButtonColor: '#059669'
								});
							} else if (data === "td") {
								swal({
									type: 'error',
									title: 'Tidak Terdaftar',
									text: 'Username tidak ditemukan dalam sistem.',
									confirmButtonColor: '#059669'
								});
							} else if (data === "nologin") {
								swal({
									type: 'warning',
									title: 'Akun Sedang Aktif',
									text: 'Akun ini sedang aktif di perangkat lain.',
									confirmButtonColor: '#059669'
								});
							} else if (data === "ta") {
								swal({
									type: 'warning',
									title: 'Belum Aktif',
									text: 'Silahkan hubungi panitia untuk aktivasi akun.',
									confirmButtonColor: '#059669'
								});
							} else {
								swal({
									type: 'error',
									title: 'Gagal Masuk',
									text: 'Terjadi kesalahan (' + data + ').',
									confirmButtonColor: '#059669'
								});
							}
						}
					},
					error: function() {
						btn.prop('disabled', false);
						btnText.text('Sign In');
						btnSpinner.addClass('hidden');

						swal({
							type: 'error',
							title: 'Gangguan Jaringan',
							text: 'Tidak dapat terhubung ke server.',
							confirmButtonColor: '#059669'
						});
					}
				});
				return false;
			});
		});
	</script>
</body>

</html>