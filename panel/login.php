<?php
require("../config/config.default.php");
require("../config/config.function.php");
require("../config/config.candy.php");

$namaaplikasi = $setting['aplikasi'];
$namasekolah = $setting['sekolah'];
$logo_src = !empty($setting['logo']) ? "$homeurl/{$setting['logo']}" : "$homeurl/dist/img/tutwuri.jpg";
$bc_src = !empty($setting['bc']) ? "$homeurl/{$setting['bc']}" : "$homeurl/dist/img/bc.jpg";
?>
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Pengawas | <?= htmlspecialchars($setting['aplikasi']) ?></title>
	<link rel="icon" type="image/png" href="../favicon.ico" />
	<!-- Local Offline Assets -->
	<link rel="stylesheet" href="<?= $homeurl ?>/dist/css/tailwind.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/izitoast/css/iziToast.min.css">
	<style>
		@keyframes floatOrbAdmin {
			0%, 100% { transform: translate(0, 0) scale(1); }
			50% { transform: translate(-25px, 30px) scale(1.05); }
		}
		.animate-orb-admin-1 { animation: floatOrbAdmin 14s ease-in-out infinite; }
		.animate-orb-admin-2 { animation: floatOrbAdmin 18s ease-in-out infinite reverse; }
	</style>
</head>

<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased relative flex items-center justify-center p-4 sm:p-6 overflow-x-hidden selection:bg-indigo-500 selection:text-white">

	<!-- Ambient Dynamic Background with Uploaded BC integration -->
	<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
		<!-- Uploaded Background Image from Admin with Dark Overlay -->
		<div class="absolute inset-0 bg-cover bg-center opacity-15 filter blur-sm scale-105" style="background-image: url('<?= $bc_src ?>');"></div>
		<div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900/95 to-indigo-950/90"></div>
		
		<!-- Floating Glowing Ambient Orbs -->
		<div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl animate-orb-admin-1"></div>
		<div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-purple-600/20 blur-3xl animate-orb-admin-2"></div>
	</div>

	<!-- Main Centered Login Card (Admin & Pengawas) -->
	<main class="relative z-10 w-full max-w-md my-auto">
		<div class="bg-slate-900/80 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 border border-slate-700/50 text-center transition-all duration-300">
			
			<!-- School Logo with Clean Elevated Avatar -->
			<div class="inline-flex relative mb-3 group">
				<div class="w-18 h-18 sm:w-20 sm:h-20 bg-white rounded-2xl p-2.5 shadow-lg ring-1 ring-white/20 flex items-center justify-center overflow-hidden">
					<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 50px; max-height: 50px; width: 100%; height: 100%; object-fit: contain;">
				</div>
			</div>

			<!-- School Name & App Title (Clean, Authentic, No Fluff) -->
			<div class="mb-6 space-y-1">
				<h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight leading-snug">
					<?= htmlspecialchars($namasekolah) ?>
				</h1>
				<p class="text-xs text-slate-400 font-medium tracking-wide">
					<?= htmlspecialchars($namaaplikasi) ?>
				</p>
				<div>
					<span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wider uppercase bg-indigo-500/10 border border-indigo-500/25 text-indigo-300">
						Pengawas & Admin
					</span>
				</div>
			</div>

			<!-- Form Login Admin -->
			<form id="form-login" action="ceklogin.php" method="POST" class="space-y-4 text-left" autocomplete="off">
				
				<!-- Field Username -->
				<div class="space-y-1.5">
					<label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 ml-1">
						Username
					</label>
					<div class="relative rounded-2xl group">
						<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-400 transition-colors">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
							</svg>
						</div>
						<input type="text" id="username" name="username" required autocomplete="username" placeholder="Masukkan Username..."
							class="w-full pl-11 pr-4 py-3 sm:py-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-slate-800 transition-all shadow-xs">
					</div>
				</div>

				<!-- Field Password -->
				<div class="space-y-1.5">
					<label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 ml-1">
						Password
					</label>
					<div class="relative rounded-2xl group">
						<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-400 transition-colors">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
							</svg>
						</div>
						<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan Password..."
							class="w-full pl-11 pr-11 py-3 sm:py-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-slate-800 transition-all shadow-xs">
						
						<!-- Toggle Password Visibility -->
						<button type="button" id="btnShowPassAdmin" tabindex="-1" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-indigo-400 transition-colors focus:outline-none" title="Lihat Password">
							<svg id="eyeOpenAdmin" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
							</svg>
							<svg id="eyeClosedAdmin" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
							</svg>
						</button>
					</div>
				</div>

				<!-- Tombol Submit -->
				<div class="pt-2">
					<button type="submit" id="btnSubmitAdmin" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 hover:from-indigo-500 hover:via-indigo-600 hover:to-purple-500 active:scale-[0.98] text-white font-semibold text-sm sm:text-base shadow-lg shadow-indigo-600/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
						<span id="btnTextAdmin">Masuk</span>
						<svg id="btnSpinnerAdmin" class="w-5 h-5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
						<svg id="btnArrowAdmin" class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
						</svg>
					</button>
				</div>
			</form>

			<!-- Minimal Clean Footer -->
			<div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400 font-medium">
				<a href="<?= $homeurl ?>" class="text-slate-400 hover:text-indigo-300 transition-colors flex items-center gap-1">
					<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
					Portal Siswa
				</a>
				<span>Versi <?= htmlspecialchars(VERSI . " r" . REVISI) ?></span>
			</div>

		</div>
	</main>

	<!-- Local Offline Scripts -->
	<script src="<?= $homeurl ?>/plugins/jQuery/jquery-3.2.1.min.js"></script>
	<script src="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.js"></script>
	<script src="<?= $homeurl ?>/plugins/izitoast/js/iziToast.min.js"></script>
	<script>
		$(document).ready(function() {
			// Password visibility toggle
			$('#btnShowPassAdmin').click(function(e) {
				e.preventDefault();
				var passInput = $('#password');
				var isPassword = passInput.attr('type') === 'password';
				passInput.attr('type', isPassword ? 'text' : 'password');
				$('#eyeOpenAdmin').toggleClass('hidden', isPassword);
				$('#eyeClosedAdmin').toggleClass('hidden', !isPassword);
			});

			// Form login Admin AJAX
			$('#form-login').submit(function(e) {
				e.preventDefault();
				var form = $(this);
				var btn = $('#btnSubmitAdmin');
				var btnText = $('#btnTextAdmin');
				var btnSpinner = $('#btnSpinnerAdmin');
				var btnArrow = $('#btnArrowAdmin');

				btn.prop('disabled', true);
				btnText.text('Memverifikasi...');
				btnSpinner.removeClass('hidden');
				btnArrow.addClass('hidden');

				$.ajax({
					type: 'POST',
					url: 'ceklogin.php',
					data: form.serialize(),
					success: function(data) {
						data = (data || '').trim();

						if (data === "ok") {
							btnText.text('Berhasil Masuk!');
							iziToast.success({
								title: 'Login Berhasil!',
								message: 'Selamat datang, mengalihkan ke dashboard...',
								position: 'topRight',
								timeout: 1500
							});
							setTimeout(function() {
								location.href = '.';
							}, 1200);
						} else {
							btn.prop('disabled', false);
							btnText.text('Masuk');
							btnSpinner.addClass('hidden');
							btnArrow.removeClass('hidden');

							if (data === "nopass") {
								iziToast.error({
									title: 'Akses Ditolak',
									message: 'Password yang Anda masukkan salah.',
									position: 'topRight',
									timeout: 3000
								});
							} else if (data === "td") {
								iziToast.warning({
									title: 'Tidak Ditemukan',
									message: 'Akun pengawas/guru tidak terdaftar dalam sistem.',
									position: 'topRight',
									timeout: 3000
								});
							} else {
								iziToast.error({
									title: 'Gagal Masuk',
									message: 'Terjadi kesalahan (' + data + ').',
									position: 'topRight',
									timeout: 3000
								});
							}
						}
					},
					error: function() {
						btn.prop('disabled', false);
						btnText.text('Masuk');
						btnSpinner.addClass('hidden');
						btnArrow.removeClass('hidden');

						iziToast.error({
							title: 'Koneksi Terputus',
							message: 'Gagal menghubungi server CBT.',
							position: 'topRight'
						});
					}
				});
				return false;
			});
		});
	</script>
</body>

</html>