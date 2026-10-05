<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') &middot; {{ config('laundry.app.nama_aplikasi') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<div class="login-shell">
    <div class="bubble" style="width:120px;height:120px;top:8%;left:6%;"></div>
    <div class="bubble" style="width:70px;height:70px;bottom:12%;right:8%;animation-delay:1.5s"></div>
    <div class="bubble" style="width:45px;height:45px;top:22%;right:14%;animation-delay:.8s"></div>

    <div class="login-card">
        <div class="row g-0">
            {{-- Panel visual --}}
            <div class="col-lg-6 login-visual p-4 p-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon" style="width:48px;height:48px;">
                            <i class="bi bi-droplet-half"></i>
                        </span>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ config('laundry.app.nama_aplikasi') }}</div>
                            <small style="opacity:.85">{{ config('laundry.app.tagline') }}</small>
                        </div>
                    </div>

                    <h2 class="fw-bold mt-5 mb-3" style="font-size:1.75rem;line-height:1.25;">
                        Cuci rapi,<br>hitung cepat,<br><span style="opacity:.8">bisnis lancar.</span>
                    </h2>
                    <p class="mb-4" style="opacity:.85;font-size:.95rem;">
                        Kelola toko, atur jasa laundry, buat UID kasir, dan catat transaksi dalam satu aplikasi.
                    </p>
                </div>

                <div class="row g-2 mt-4">
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center bg-white bg-opacity-75">
                            <i class="bi bi-shield-lock fs-4"></i>
                            <div class="small fw-bold mt-1">Multi Role</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center bg-white bg-opacity-75">
                            <i class="bi bi-receipt fs-4"></i>
                            <div class="small fw-bold mt-1">Invoice</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center bg-white bg-opacity-75">
                            <i class="bi bi-file-earmark-bar-graph fs-4"></i>
                            <div class="small fw-bold mt-1">Laporan</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form login --}}
            <div class="col-lg-6 p-4 p-lg-5">
                <div class="mb-4">
                    <h3 class="fw-bold mb-1">Selamat Datang</h3>
                    <p class="text-muted mb-0">Silakan masuk untuk melanjutkan.</p>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-octagon-fill"></i> {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success py-2 small d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" autocomplete="off" class="fade-up">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="username">Username / UID kasir</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-color:var(--app-border)">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username') }}"
                                   placeholder="Username owner atau UID kasir" autofocus>
                        </div>
                        @error('username')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-color:var(--app-border)">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Masukkan password">
                            <button class="btn btn-outline-sky border-start-0" type="button" id="togglePassword" title="Lihat password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label small" for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn btn-sky w-100 py-2">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>
                </form>

                <div class="divider-sky my-4"></div>

                <p class="small text-muted">Belum terdaftar? <a href="{{ route('daftar-toko.create') }}">Ajukan pendaftaran toko</a>.</p>

                <div class="small text-muted">
                    <div class="fw-bold mb-2 text-uppercase" style="font-size:.68rem;letter-spacing:.1em;">Hak Akses</div>
                    <ul class="list-unstyled mb-0 d-grid gap-2">
                        <li class="d-flex gap-2">
                            <span class="badge-role" style="background:rgba(14,165,233,.12);color:#0284c7;">ADMIN</span>
                            <span>Kelola toko dan akun owner</span>
                        </li>
                        <li class="d-flex gap-2">
                            <span class="badge-role" style="background:rgba(16,185,129,.12);color:#059669;">KASIR</span>
                            <span>Catat dan edit transaksi toko</span>
                        </li>
                        <li class="d-flex gap-2">
                            <span class="badge-role" style="background:rgba(16,185,129,.12);color:#147d64;">OWNER</span>
                            <span>Kelola profil, jasa, dan kasir toko</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
    document.getElementById('togglePassword')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>