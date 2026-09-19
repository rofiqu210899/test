<?php
require("../config/config.default.php");
require("../config/config.function.php");
require("../config/config.candy.php");

$namaaplikasi = $setting['aplikasi'];
$namasekolah = $setting['sekolah'];
?>
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Pengawas & Admin | <?= htmlspecialchars(APLIKASI . " v" . VERSI . " r" . REVISI) ?></title>
	<link rel="icon" type="image/png" href="../favicon.ico" />
	<!-- Local Offline Assets -->
	<link rel="stylesheet" href="<?= $homeurl ?>/dist/css/tailwind.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/izitoast/css/iziToast.min.css">
	<style>
		@keyframes floatOrbAdmin {
			0%, 100% { transform: translate(0, 0) scale(1); }
			50% { transform: translate(-30px, 35px) scale(1.06); }
		}
		.animate-orb-admin-1 { animation: floatOrbAdmin 14s ease-in-out infinite; }
		.animate-orb-admin-2 { animation: floatOrbAdmin 18s ease-in-out infinite reverse; }
	</style>
</head>

<body class="min-h-screen bg-slate-950 font-sans text-slate-800 antialiased relative flex items-center justify-center p-4 sm:p-6 lg:p-10 overflow-x-hidden selection:bg-indigo-500 selection:text-white">

	<!-- Ambient Dynamic Background -->
	<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
		<?php if (!empty($setting['bc']) && file_exists("../" . $setting['bc'])) : ?>
			<div class="absolute inset-0 bg-cover bg-center opacity-15 scale-105 filter blur-sm transition-transform duration-1000" style="background-image: url('../<?= htmlspecialchars($setting['bc']) ?>');"></div>
		<?php endif; ?>
		<!-- Deep Tech Gradient Mesh -->
		<div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950"></div>
		
		<!-- Floating Glowing Ambient Orbs -->
		<div class="absolute top-10 left-10 w-[28rem] h-[28rem] rounded-full bg-indigo-600/20 blur-[100px] animate-orb-admin-1"></div>
		<div class="absolute bottom-10 right-10 w-[30rem] h-[30rem] rounded-full bg-violet-600/20 blur-[110px] animate-orb-admin-2"></div>
		<div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/10 via-transparent to-transparent"></div>
	</div>

	<!-- Main Card Container (Dual-Pane on Desktop) -->
	<main class="relative z-10 w-full max-w-4xl my-auto">
		<div class="glass-card-dark rounded-3xl shadow-2xl shadow-black/60 border border-slate-700/50 backdrop-blur-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 transition-all duration-300">
			
			<!-- Left Brand Showcase Panel (5 cols on LG) -->
			<div class="lg:col-span-5 bg-gradient-to-b from-indigo-950/70 via-slate-900/80 to-slate-950/90 p-6 sm:p-8 lg:p-10 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800/80 relative overflow-hidden">
				
				<!-- Subtle inner glow decoration -->
				<div class="absolute -top-20 -left-20 w-48 h-48 bg-indigo-500/30 rounded-full blur-2xl pointer-events-none"></div>

				<!-- Top Brand & School Badge -->
				<div class="relative z-10 space-y-4">
					<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold tracking-wider uppercase">
						<span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
						Portal Pengawas & Guru
					</div>

					<div class="flex items-center gap-3.5 pt-1">
						<div class="w-14 h-14 bg-white/95 rounded-2xl p-2 shadow-lg ring-2 ring-white/10 flex items-center justify-center shrink-0 overflow-hidden">
							<?php $logo_src = !empty($setting['logo']) ? "$homeurl/{$setting['logo']}" : "$homeurl/dist/img/tutwuri.jpg"; ?>
							<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo CBT" style="max-width: 44px; max-height: 44px; width: 100%; height: 100%; object-fit: contain;">
						</div>
						<div>
							<h1 class="text-lg font-bold text-white tracking-tight leading-tight">
								<?= htmlspecialchars($namaaplikasi) ?>
							</h1>
							<p class="text-xs text-slate-400 font-medium line-clamp-1">
								<?= htmlspecialchars($namasekolah) ?>
							</p>
						</div>
					</div>

					<p class="text-xs text-slate-300/80 leading-relaxed pt-2">
						Sistem Computer-Based Testing terpadu untuk pelaksanaan, pengawasan, dan rekapitulasi ujian siswa secara akurat dan real-time.
					</p>
				</div>

				<!-- Middle Features Badges (Desktop Only) -->
				<div class="relative z-10 hidden sm:grid grid-cols-1 gap-2.5 my-6">
					<div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-800/40 border border-slate-700/40 text-xs text-slate-300">
						<div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
						</div>
						<span>Enkripsi Password & Sesi Aman</span>
					</div>
					<div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-800/40 border border-slate-700/40 text-xs text-slate-300">
						<div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
						</div>
						<span>AI Question Analysis & Audit</span>
					</div>
				</div>

				<!-- Bottom Location & Info -->
				<div class="relative z-10 pt-4 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
					<span class="truncate"><?= htmlspecialchars("$setting[kecamatan] - $setting[kota]") ?></span>
					<span class="text-indigo-400 font-mono text-[11px]">v<?= htmlspecialchars(VERSI) ?></span>
				</div>
			</div>

			<!-- Right Login Form Panel (7 cols on LG) -->
			<div class="lg:col-span-7 p-6 sm:p-8 lg:p-12 flex flex-col justify-center bg-slate-900/60 backdrop-blur-md">
				
				<div class="mb-7 text-left">
					<h2 class="text-2xl font-bold text-white tracking-tight">
						Masuk ke Panel
					</h2>
					<p class="text-xs sm:text-sm text-slate-400 mt-1">
						Masukkan kredensial akun Pengawas, Guru, atau Administrator Anda
					</p>
				</div>

				<!-- Form Login Admin -->
				<form id="form-login" action="ceklogin.php" method="POST" class="space-y-4 sm:space-y-5" autocomplete="off">
					
					<!-- Username Field -->
					<div class="space-y-1.5 text-left">
						<label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 ml-1">
							Username
						</label>
						<div class="relative rounded-2xl group">
							<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-400 transition-colors">
								<!-- Shield User Icon -->
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
								</svg>
							</div>
							<input type="text" id="username" name="username" required autocomplete="username" placeholder="Masukkan username..."
								class="glass-input-dark w-full pl-11 pr-4 py-3 sm:py-3.5 rounded-2xl placeholder-slate-500 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-400 shadow-xs">
						</div>
					</div>

					<!-- Password Field -->
					<div class="space-y-1.5 text-left">
						<label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 ml-1">
							Password
						</label>
						<div class="relative rounded-2xl group">
							<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-400 transition-colors">
								<!-- Key Lock Icon -->
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
								</svg>
							</div>
							<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan password..."
								class="glass-input-dark w-full pl-11 pr-11 py-3 sm:py-3.5 rounded-2xl placeholder-slate-500 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-400 shadow-xs">
							
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

					<!-- Submit Button -->
					<div class="pt-2">
						<button type="submit" id="btnSubmitAdmin" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 hover:from-indigo-500 hover:via-indigo-600 hover:to-purple-500 active:scale-[0.98] text-white font-semibold text-sm sm:text-base shadow-lg shadow-indigo-600/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
							<span id="btnTextAdmin">Masuk ke Dashboard</span>
							<!-- Spinner -->
							<svg id="btnSpinnerAdmin" class="w-5 h-5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
							<!-- Arrow -->
							<svg id="btnArrowAdmin" class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
							</svg>
						</button>
					</div>

				</form>

				<!-- Switch Portal Link -->
				<div class="mt-8 pt-5 border-t border-slate-800/80 flex items-center justify-between text-xs">
					<a href="<?= $homeurl ?>" class="text-slate-400 hover:text-indigo-400 hover:underline transition-colors flex items-center gap-1.5">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
						</svg>
						Kembali ke Portal Siswa
					</a>
					<span class="text-slate-500 font-mono text-[11px]">&copy; <?= date('Y') ?> Candy CBT</span>
				</div>

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
				btnText.text('Mengautentikasi...');
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
							btnText.text('Masuk ke Dashboard');
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
						btnText.text('Masuk ke Dashboard');
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