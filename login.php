<?php
require("config/config.default.php");
require("config/config.candy.php");
?>
<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Siswa | <?= htmlspecialchars($setting['aplikasi']) ?></title>
	<link rel="icon" type="image/png" href="favicon.ico" />
	<!-- Local Offline Assets -->
	<link rel="stylesheet" href="<?= $homeurl ?>/dist/css/tailwind.min.css">
	<link rel="stylesheet" href="<?= $homeurl ?>/plugins/sweetalert2/dist/sweetalert2.min.css">
	<style>
		@keyframes floatOrb {
			0%, 100% { transform: translate(0, 0) scale(1); }
			33% { transform: translate(30px, -40px) scale(1.08); }
			66% { transform: translate(-25px, 25px) scale(0.95); }
		}
		.animate-orb-1 { animation: floatOrb 12s ease-in-out infinite; }
		.animate-orb-2 { animation: floatOrb 15s ease-in-out infinite reverse; }
		.animate-orb-3 { animation: floatOrb 18s ease-in-out infinite 2s; }
	</style>
</head>

<body class="min-h-screen bg-slate-900 font-sans text-slate-800 antialiased relative flex items-center justify-center p-4 sm:p-6 overflow-x-hidden selection:bg-indigo-500 selection:text-white">

	<!-- Ambient Dynamic Background with School Background Fallback -->
	<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
		<?php if (!empty($setting['bc']) && file_exists($setting['bc'])) : ?>
			<div class="absolute inset-0 bg-cover bg-center opacity-20 scale-105 filter blur-sm transition-transform duration-1000" style="background-image: url('<?= htmlspecialchars($setting['bc']) ?>');"></div>
		<?php endif; ?>
		<!-- Soft Gradient Mesh Background -->
		<div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950"></div>
		
		<!-- Floating Glassmorphic Ambient Orbs -->
		<div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-indigo-500/25 blur-3xl animate-orb-1"></div>
		<div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-purple-500/25 blur-3xl animate-orb-2"></div>
		<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[32rem] h-[32rem] rounded-full bg-blue-600/15 blur-3xl animate-orb-3"></div>
		<div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white/5 via-transparent to-transparent"></div>
	</div>

	<!-- Main Login Container -->
	<main class="relative z-10 w-full max-w-md my-auto">
		<!-- Glassmorphism Card -->
		<div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl shadow-indigo-950/50 border border-white/60 backdrop-blur-2xl transition-all duration-300">
			
			<!-- Header Section -->
			<div class="text-center mb-7">
				<!-- School Logo with Glow Ring -->
				<div class="inline-flex relative mb-4 group">
					<div class="absolute -inset-1.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur-sm opacity-60 group-hover:opacity-100 transition duration-300"></div>
					<div class="relative w-18 h-18 sm:w-20 sm:h-20 bg-white/95 rounded-2xl p-2.5 shadow-md flex items-center justify-center ring-2 ring-white/80">
						<img src="<?= htmlspecialchars($setting['logo']) ?>" alt="Logo Sekolah" class="max-h-full max-w-full object-contain filter drop-shadow-sm">
					</div>
				</div>

				<!-- Titles & Badges -->
				<div class="space-y-1">
					<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100/90 text-indigo-700 tracking-wide ring-1 ring-indigo-500/20 shadow-xs mb-1">
						PORTAL UJIAN SISWA
					</span>
					<h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight leading-snug">
						<?= htmlspecialchars($setting['sekolah']) ?>
					</h1>
					<p class="text-xs sm:text-sm text-slate-600 font-normal">
						Silakan masuk dengan akun ujian Anda
					</p>
				</div>
			</div>

			<!-- Form Login Siswa -->
			<form id="formlogin" action="ceklogin.php" method="POST" class="space-y-4 sm:space-y-5" autocomplete="off">
				
				<!-- Field Username -->
				<div class="space-y-1.5 text-left">
					<label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 ml-1">
						Username / Nomor Peserta
					</label>
					<div class="relative rounded-2xl group">
						<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
							<!-- User Icon SVG -->
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
							</svg>
						</div>
						<input type="text" id="username" name="username" required autocomplete="username" placeholder="Masukkan Username..."
							class="glass-input w-full pl-11 pr-4 py-3 sm:py-3.5 rounded-2xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 shadow-xs">
					</div>
				</div>

				<!-- Field Password -->
				<div class="space-y-1.5 text-left">
					<label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 ml-1">
						Password
					</label>
					<div class="relative rounded-2xl group">
						<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
							<!-- Lock Icon SVG -->
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
							</svg>
						</div>
						<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan Password..."
							class="glass-input w-full pl-11 pr-11 py-3 sm:py-3.5 rounded-2xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 shadow-xs">
						<!-- Toggle Password Visibility -->
						<button type="button" id="btnShowPass" tabindex="-1" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none" title="Lihat Password">
							<!-- Eye Open Icon -->
							<svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
							</svg>
							<!-- Eye Closed Icon (Hidden Initially) -->
							<svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
							</svg>
						</button>
					</div>
				</div>

				<!-- Tombol Submit -->
				<div class="pt-2">
					<button type="submit" id="btnSubmit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 hover:from-indigo-500 hover:via-indigo-600 hover:to-purple-500 active:scale-[0.98] text-white font-semibold text-sm sm:text-base shadow-lg shadow-indigo-600/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
						<span id="btnText">Masuk Sekarang</span>
						<!-- Spinner (Hidden Initially) -->
						<svg id="btnSpinner" class="w-5 h-5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
						<!-- Arrow Icon -->
						<svg id="btnArrow" class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
						</svg>
					</button>
				</div>
			</form>

			<!-- Divider & Footer Info -->
			<div class="mt-6 pt-5 border-t border-slate-200/60 text-center space-y-3">
				<div class="flex items-center justify-between text-xs text-slate-500">
					<span>Versi <?= htmlspecialchars(VERSI . " r" . REVISI) ?></span>
					<a href="<?= $homeurl ?>/panel/login.php" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline transition-colors flex items-center gap-1">
						Portal Pengawas &rarr;
					</a>
				</div>
				<p class="text-[11px] text-slate-400">
					Supported by <?= htmlspecialchars(APLIKASI) ?> &bull; Computer-Based Testing System
				</p>
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
				var btnArrow = $('#btnArrow');

				// Disable and show loading
				btn.prop('disabled', true);
				btnText.text('Memverifikasi...');
				btnSpinner.removeClass('hidden');
				btnArrow.addClass('hidden');

				$.ajax({
					type: 'POST',
					url: form.attr('action'),
					data: form.serialize(),
					success: function(data) {
						data = (data || '').trim();

						if (data === "ok") {
							btnText.text('Berhasil Masuk!');
							swal({
								title: 'Login Berhasil!',
								text: 'Mengalihkan ke beranda ujian...',
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
							// Reset button
							btn.prop('disabled', false);
							btnText.text('Masuk Sekarang');
							btnSpinner.addClass('hidden');
							btnArrow.removeClass('hidden');

							if (data === "nopass") {
								swal({
									type: 'warning',
									title: 'Password Salah',
									text: 'Periksa kembali password akun ujian Anda.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "td") {
								swal({
									type: 'error',
									title: 'Siswa Tidak Terdaftar',
									text: 'Username atau nomor peserta tidak ditemukan dalam sistem.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "nologin") {
								swal({
									type: 'warning',
									title: 'Siswa Sudah Aktif',
									text: 'Akun ini sedang aktif di perangkat lain. Hubungi proktor jika ingin reset login.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "ta") {
								swal({
									type: 'warning',
									title: 'Akun Belum Diaktifkan',
									text: 'Silahkan hubungi panitia ujian untuk aktivasi akun.',
									confirmButtonColor: '#4f46e5'
								});
							} else {
								swal({
									type: 'error',
									title: 'Gagal Masuk',
									text: 'Terjadi kesalahan sistem (' + data + '). Coba lagi nanti.',
									confirmButtonColor: '#4f46e5'
								});
							}
						}
					},
					error: function() {
						btn.prop('disabled', false);
						btnText.text('Masuk Sekarang');
						btnSpinner.addClass('hidden');
						btnArrow.removeClass('hidden');

						swal({
							type: 'error',
							title: 'Gangguan Jaringan',
							text: 'Tidak dapat terhubung ke server CBT.',
							confirmButtonColor: '#4f46e5'
						});
					}
				});
				return false;
			});
		});
	</script>
</body>

</html>