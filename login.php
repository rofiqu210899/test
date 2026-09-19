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
</head>

<body class="min-h-screen bg-slate-900 font-sans text-slate-800 antialiased relative flex items-center justify-center p-4 sm:p-6 lg:p-10 overflow-x-hidden selection:bg-indigo-500 selection:text-white">

	<!-- Ambient Dynamic Background using uploaded BC -->
	<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
		<div class="absolute inset-0 bg-cover bg-center opacity-25 filter blur-md scale-105" style="background-image: url('<?= $bc_src ?>');"></div>
		<div class="absolute inset-0 bg-gradient-to-br from-slate-950/85 via-slate-900/90 to-indigo-950/85"></div>
	</div>

	<!-- Main Login Container (Dual-Pane Split Card) -->
	<main class="relative z-10 w-full max-w-4xl my-auto">
		<div class="glass-card rounded-3xl shadow-2xl shadow-black/40 border border-white/40 backdrop-blur-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12">
			
			<!-- Left Column: Uploaded Background Image (5 cols on LG) -->
			<div class="relative lg:col-span-5 bg-slate-950 flex flex-col justify-between overflow-hidden min-h-[220px] lg:min-h-[460px]">
				<!-- Background Image from Admin Upload -->
				<img src="<?= $bc_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/bc.jpg';" alt="Background" class="absolute inset-0 w-full h-full object-cover">
				<div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-slate-950/30"></div>

				<!-- Clean School Brand at Bottom -->
				<div class="relative z-10 p-5 sm:p-6 mt-auto flex items-center gap-3">
					<div class="w-12 h-12 rounded-xl bg-white/95 p-1.5 shadow-md ring-2 ring-white/20 flex items-center justify-center shrink-0 overflow-hidden">
						<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo Sekolah" style="max-width: 38px; max-height: 38px; width: 100%; height: 100%; object-fit: contain;">
					</div>
					<div class="text-left">
						<h1 class="text-sm sm:text-base font-bold text-white tracking-tight leading-snug line-clamp-1">
							<?= htmlspecialchars($setting['sekolah']) ?>
						</h1>
						<p class="text-[11px] text-slate-300 font-medium">
							<?= htmlspecialchars($setting['aplikasi']) ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Right Column: Login Form (7 cols on LG) -->
			<div class="lg:col-span-7 p-6 sm:p-8 lg:p-12 flex flex-col justify-center bg-white/90 backdrop-blur-xl text-left">
				
				<!-- Clean Header -->
				<div class="mb-6">
					<div class="lg:hidden mb-3 flex items-center gap-2.5">
						<div class="w-9 h-9 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
							<img src="<?= $logo_src ?>" onerror="this.onerror=null; this.src='<?= $homeurl ?>/dist/img/tutwuri.jpg';" alt="Logo" style="max-width: 28px; max-height: 28px; width: 100%; height: 100%; object-fit: contain;">
						</div>
						<span class="text-xs font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($setting['sekolah']) ?></span>
					</div>
					<h2 class="text-2xl font-bold text-slate-900 tracking-tight">
						Masuk
					</h2>
				</div>

				<!-- Form Login Siswa -->
				<form id="formlogin" action="ceklogin.php" method="POST" class="space-y-4" autocomplete="off">
					
					<!-- Field Username -->
					<div class="space-y-1.5 text-left">
						<label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 ml-1">
							Username
						</label>
						<div class="relative rounded-2xl group">
							<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
								</svg>
							</div>
							<input type="text" id="username" name="username" required autocomplete="username" placeholder="Username..."
								class="glass-input w-full pl-11 pr-4 py-3.5 rounded-2xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 shadow-xs">
						</div>
					</div>

					<!-- Field Password -->
					<div class="space-y-1.5 text-left">
						<label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 ml-1">
							Password
						</label>
						<div class="relative rounded-2xl group">
							<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
								</svg>
							</div>
							<input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Password..."
								class="glass-input w-full pl-11 pr-11 py-3.5 rounded-2xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/15 focus:border-indigo-500 shadow-xs">
							
							<!-- Toggle Password Visibility -->
							<button type="button" id="btnShowPass" tabindex="-1" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none" title="Lihat Password">
								<svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
								</svg>
								<svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
								</svg>
							</button>
						</div>
					</div>

					<!-- Tombol Submit -->
					<div class="pt-2">
						<button type="submit" id="btnSubmit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 hover:from-indigo-500 hover:via-indigo-600 hover:to-purple-500 active:scale-[0.98] text-white font-semibold text-sm sm:text-base shadow-lg shadow-indigo-600/25 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
							<span id="btnText">Masuk</span>
							<svg id="btnSpinner" class="w-5 h-5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
							<svg id="btnArrow" class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
							</svg>
						</button>
					</div>
				</form>

				<!-- Minimal Clean Footer -->
				<div class="mt-8 pt-5 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-400">
					<span><?= htmlspecialchars(VERSI . " r" . REVISI) ?></span>
					<span>&copy; <?= date('Y') ?> <?= htmlspecialchars(APLIKASI) ?></span>
				</div>

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
							btnText.text('Berhasil');
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
							btnText.text('Masuk');
							btnSpinner.addClass('hidden');
							btnArrow.removeClass('hidden');

							if (data === "nopass") {
								swal({
									type: 'warning',
									title: 'Password Salah',
									text: 'Periksa kembali password akun Anda.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "td") {
								swal({
									type: 'error',
									title: 'Tidak Terdaftar',
									text: 'Username tidak ditemukan.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "nologin") {
								swal({
									type: 'warning',
									title: 'Akun Sedang Aktif',
									text: 'Akun ini sedang aktif di perangkat lain.',
									confirmButtonColor: '#4f46e5'
								});
							} else if (data === "ta") {
								swal({
									type: 'warning',
									title: 'Belum Aktif',
									text: 'Silahkan hubungi panitia untuk aktivasi akun.',
									confirmButtonColor: '#4f46e5'
								});
							} else {
								swal({
									type: 'error',
									title: 'Gagal Masuk',
									text: 'Terjadi kesalahan (' + data + ').',
									confirmButtonColor: '#4f46e5'
								});
							}
						}
					},
					error: function() {
						btn.prop('disabled', false);
						btnText.text('Masuk');
						btnSpinner.addClass('hidden');
						btnArrow.removeClass('hidden');

						swal({
							type: 'error',
							title: 'Gangguan Jaringan',
							text: 'Tidak dapat terhubung ke server.',
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