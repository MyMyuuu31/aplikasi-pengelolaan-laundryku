<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ config('laundry.app.nama_aplikasi') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        @font-face {
            font-display: block;
            font-family: bootstrap-icons;
            src: url("{{ asset('assets/fonts/bootstrap-icons.woff2') }}") format("woff2"),
                url("{{ asset('assets/fonts/bootstrap-icons.woff') }}") format("woff");
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex min-vh-100">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="app-sidebar flex-column flex-shrink-0 p-3 position-sticky top-0" id="appSidebar" style="height: 100vh;">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-sky-900 mb-4 px-2">
            <span class="stat-icon" style="width:42px;height:42px;">
                <i class="bi bi-droplet-half"></i>
            </span>
            <span class="brand-title">
                <span class="d-block fw-bold fs-5 lh-1">{{ config('laundry.app.nama_aplikasi') }}</span>
                <span class="d-block" style="font-size:.7rem;opacity:.75;">{{ config('laundry.app.tagline') }}</span>
            </span>
        </a>

        @php
            $user = auth()->user();
            $menu = [
                ['label' => 'Menu Utama'],
                [
                    'route' => 'dashboard', 'icon' => 'bi-speedometer2', 'text' => 'Dashboard',
                    'roles' => ['admin', 'kasir', 'owner'],
                ],
                [
                    'label' => 'Operasional', 'roles' => ['owner', 'kasir'],
                ],
                [
                    'route' => 'transaksi.index', 'icon' => 'bi-receipt', 'text' => 'Data Transaksi',
                    'roles' => ['owner', 'kasir'],
                ],
                [
                    'route' => 'transaksi.create', 'icon' => 'bi-plus-square', 'text' => 'Entri Laundry Baru',
                    'roles' => ['owner', 'kasir'],
                ],
                [
                    'label' => 'Laporan', 'roles' => ['kasir', 'owner'],
                ],
                ['route' => 'laporan.index', 'icon' => 'bi-graph-up-arrow', 'text' => 'Ringkasan Laporan', 'roles' => ['kasir', 'owner']],
                ['route' => 'laporan.transaksi', 'icon' => 'bi-journal-text', 'text' => 'Laporan Transaksi', 'roles' => ['kasir', 'owner']],
                ['route' => 'laporan.pendapatan', 'icon' => 'bi-cash-coin', 'text' => 'Laporan Pendapatan', 'roles' => ['kasir', 'owner']],
                ['route' => 'laporan.paket', 'icon' => 'bi-box-seam', 'text' => 'Laporan Paket', 'roles' => ['kasir', 'owner']],
                ['route' => 'laporan.member', 'icon' => 'bi-people', 'text' => 'Laporan Member', 'roles' => ['kasir', 'owner']],
                [
                    'label' => 'Kelola Toko', 'roles' => ['owner'],
                ],
                ['route' => 'toko.edit', 'icon' => 'bi-shop', 'text' => 'Profil Toko', 'roles' => ['owner']],
                ['route' => 'paket.index', 'icon' => 'bi-tags', 'text' => 'Jasa & Harga', 'roles' => ['owner']],
                ['route' => 'kasir.index', 'icon' => 'bi-person-badge', 'text' => 'Akun Kasir', 'roles' => ['owner']],
                ['route' => 'member.index', 'icon' => 'bi-person-vcard', 'text' => 'Data Pelanggan', 'roles' => ['owner', 'kasir']],
                [
                    'label' => 'Platform', 'roles' => ['admin'],
                ],
                ['route' => 'outlet.index', 'icon' => 'bi-shop', 'text' => 'Toko Laundry', 'roles' => ['admin']],
                ['route' => 'user.index', 'icon' => 'bi-person-gear', 'text' => 'Akun Owner', 'roles' => ['admin']],
                ['route' => 'pengajuan-toko.index', 'icon' => 'bi-inbox', 'text' => 'Pengajuan Toko', 'roles' => ['admin']],
                [
                    'label' => 'Sistem', 'roles' => ['admin'],
                ],
                ['route' => 'log.index', 'icon' => 'bi-clock-history', 'text' => 'Log Aktivitas', 'roles' => ['admin']],
            ];
        @endphp

        <nav class="nav flex-column gap-1 overflow-auto">
            @foreach ($menu as $item)
                @if (isset($item['label']))
                    @if (in_array($user->role, $item['roles'] ?? [], true))
                        <div class="nav-label text-uppercase fw-bold mt-3 mb-1 px-2" style="font-size:.66rem;letter-spacing:.12em;opacity:.6;">
                            {{ $item['label'] }}
                        </div>
                    @endif
                @elseif (in_array($user->role, $item['roles'] ?? [], true))
                    <a href="{{ route($item['route']) }}"
                       class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span class="nav-text">{{ $item['text'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="mt-auto pt-3 px-2 nav-text">
            <div class="p-3 rounded-3" style="background:rgba(255,255,255,.12)">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-circle" style="width:36px;height:36px;font-size:.85rem;">{{ $user->initials() }}</span>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-truncate" style="font-size:.85rem;">{{ $user->nama }}</div>
                        <div style="font-size:.72rem;opacity:.8;">{{ $user->roleLabel() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div id="sidebarBackdrop" class="d-lg-none position-fixed top-0 start-0 w-100 h-100 d-none"
         style="background:rgba(23,68,91,.28);backdrop-filter:blur(2px);z-index:1040;"></div>

    {{-- ================= KONTEN ================= --}}
    <div class="flex-grow-1 d-flex flex-column" style="min-width:0">

        <header class="app-topbar sticky-top px-3 px-lg-4 py-2 d-flex align-items-center gap-3">
            <button class="icon-btn d-lg-none" id="sidebarToggle" type="button" aria-label="Menu">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="flex-grow-1 min-w-0">
                <h1 class="h6 fw-bold mb-0 text-truncate">@yield('page-title', 'Dashboard')</h1>
                <small class="text-muted d-none d-sm-block">@yield('page-subtitle', '') </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill text-bg-light border d-none d-md-inline-flex align-items-center gap-1"
                      style="font-size:.72rem;">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->translatedFormat('d F Y') }}
                </span>

                <div class="dropdown">
                    <button class="btn btn-outline-sky btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="avatar-circle" style="width:30px;height:30px;font-size:.72rem;">{{ $user->initials() }}</span>
                        <span class="d-none d-sm-inline">{{ $user->nama }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2">
                            <div class="fw-bold">{{ $user->nama }}</div>
                            <div class="small text-muted">{{ $user->username }} &middot; {{ $user->roleLabel() }}</div>
                            @if ($user->outlet)
                                <div class="small text-muted"><i class="bi bi-shop"></i> {{ $user->outlet->nama_outlet }}</div>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Mobile bottom nav --}}
           <div class="d-lg-none border-top bg-white position-fixed bottom-0 start-0 w-100 px-2 py-2"
               style="border-color:var(--app-border)!important;z-index:1010;">
            <div class="d-flex justify-content-around">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-text {{ request()->routeIs('dashboard') ? 'text-sky' : 'text-muted' }}"
                   style="font-size:.68rem;">
                    <i class="bi bi-speedometer2 d-block fs-5"></i>Beranda
                </a>
                @if (in_array($user->role, ['owner', 'kasir'], true))
                    <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-text text-muted" style="font-size:.68rem;">
                        <i class="bi bi-receipt d-block fs-5"></i>Transaksi
                    </a>
                    <a href="{{ route('transaksi.create') }}" class="btn btn-sm btn-text text-muted" style="font-size:.68rem;">
                        <i class="bi bi-plus-square d-block fs-5"></i>Entri
                    </a>
                @endif
                @if (in_array($user->role, ['owner', 'kasir'], true))
                    <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-text text-muted" style="font-size:.68rem;">
                        <i class="bi bi-graph-up-arrow d-block fs-5"></i>Laporan
                    </a>
                @endif
            </div>
        </div>

        <main class="app-main p-3 p-lg-4 flex-grow-1 fade-up">
            @include('partials.flash')

            @yield('content')
        </main>

        <footer class="app-footer-space text-center text-muted small py-3 px-3 border-top" style="border-color:var(--app-border)!important;">
            &copy; {{ date('Y') }} {{ config('laundry.app.nama_aplikasi') }} &middot;
            Aplikasi Pengelolaan Laundry &middot; Tugas UKK RPL Paket 3
        </footer>
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>