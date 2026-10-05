@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas laundry hari ini')

@section('content')
    @php
        $user = auth()->user();
        $hour = (int) now()->format('H');
        $salam = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    @endphp

    <section class="dashboard-welcome p-3 p-md-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-semibold small text-muted">{{ $salam }}, {{ $user->nama }} · {{ $user->roleLabel() }}</div>
                <h2 class="fw-bold mb-1 mt-1" style="font-size:1.4rem;">Operasional Toko</h2>
                <div class="small text-muted">
                    @if ($user->outlet)
                        Outlet <strong>{{ $user->outlet->nama_outlet }}</strong>
                    @else
                        Seluruh outlet
                    @endif
                    &middot; {{ $transaksiProses }} laundry sedang diproses
                </div>
            </div>

            @if (in_array($user->role, ['owner', 'kasir'], true))
                <a href="{{ route('transaksi.create') }}" class="btn btn-sky fw-bold px-3 py-2">
                    <i class="bi bi-plus-lg"></i> Entri Laundry Baru
                </a>
            @else
                <a href="{{ route('laporan.index') }}" class="btn btn-outline-sky fw-bold px-3 py-2">
                    <i class="bi bi-graph-up-arrow"></i> Buka Laporan
                </a>
            @endif
        </div>
    </section>

    {{-- Kartu statistik --}}
    <div class="row g-3 stagger mb-3">
        @php
            $cards = [
                ['icon' => 'bi-cash-stack', 'label' => 'Pendapatan Bulan Ini', 'value' => $totalPendapatan, 'prefix' => 'Rp ', 'tone' => 'success'],
                ['icon' => 'bi-receipt-cutoff', 'label' => 'Transaksi Bulan Ini', 'value' => $totalTransaksi, 'prefix' => '', 'tone' => 'primary'],
                ['icon' => 'bi-hourglass-split', 'label' => 'Sedang Diproses', 'value' => $transaksiProses, 'prefix' => '', 'tone' => 'warning'],
                ['icon' => 'bi-person-check', 'label' => 'Siap Diambil', 'value' => $transaksiSiapDiambil, 'prefix' => '', 'tone' => 'info'],
                ['icon' => 'bi-people', 'label' => 'Jumlah Member', 'value' => $totalMember, 'prefix' => '', 'tone' => 'primary'],
                ['icon' => 'bi-tags', 'label' => 'Paket Aktif', 'value' => $totalPaket, 'prefix' => '', 'tone' => 'info'],
            ];
            if ($user->isOwner()) {
                $cards[] = ['icon' => 'bi-person-badge', 'label' => 'Kasir Aktif', 'value' => $totalKasir, 'prefix' => '', 'tone' => 'mint'];
            }
        @endphp

        @foreach ($cards as $c)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card-soft stat-card p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon {{ $c['tone'] === 'mint' ? 'stat-icon-mint' : '' }}"><i class="bi {{ $c['icon'] }}"></i></span>
                        <div class="min-w-0">
                            <div class="text-muted small">{{ $c['label'] }}</div>
                            <div class="stat-value text-truncate">
                                @if ($c['prefix'] === 'Rp ')
                                    Rp {{ number_format($c['value'], 0, ',', '.') }}
                                @else
                                    <span data-counter="{{ number_format($c['value'], 0, ',', '.') }}">0</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Grafik pendapatan --}}
        <div class="col-12 col-xl-8">
            <div class="card-soft p-3 p-lg-4 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold mb-0">Pendapatan 7 Hari Terakhir</h3>
                        <small class="text-muted">Total transaksi berstatus sudah diambil</small>
                    </div>
                    <span class="badge rounded-pill text-bg-info">
                        Rp {{ number_format($grafik->sum('nilai'), 0, ',', '.') }}
                    </span>
                </div>
                <div style="height:280px"><canvas id="grafikPendapatan"></canvas></div>
            </div>
        </div>

        {{-- Grafik status --}}
        <div class="col-12 col-xl-4">
            <div class="card-soft p-3 p-lg-4 h-100">
                <h3 class="h6 fw-bold mb-1">Status Laundry</h3>
                <small class="text-muted">Komposisi status bulan berjalan</small>
                <div style="height:280px"><canvas id="grafikStatus"></canvas></div>
            </div>
        </div>

        {{-- Transaksi terbaru --}}
        <div class="col-12 col-xl-8">
            <div class="card-soft p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 fw-bold mb-0">Transaksi Terbaru</h3>
                    @if (in_array($user->role, ['admin', 'kasir'], true))
                        <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-sky">Lihat Semua</a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-sky align-middle">
                        <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($transaksiTerbaru as $t)
                            <tr>
                                <td class="fw-bold">{{ $t->no_invoice }}</td>
                                <td>{{ $t->namaPelanggan() }}</td>
                                <td class="text-muted small">{{ $t->created_at->translatedFormat('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $t->statusColor() }}">{{ $t->statusLabel() }}</span>
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada transaksi. Silakan mulai entri laundry.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paket terlaris + member aktif --}}
        <div class="col-12 col-xl-4">
            <div class="card-soft p-3 p-lg-4 mb-3">
                <h3 class="h6 fw-bold mb-3">Paket Terlaris</h3>
                @forelse ($paketTerlaris as $p)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate small">{{ $p->nama_paket }}</div>
                            <div class="text-muted" style="font-size:.74rem">{{ $p->total_qty }} {{ $p->satuan }}</div>
                        </div>
                        <div class="fw-bold small">Rp {{ number_format((float) $p->total_nilai, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">Belum ada penjualan paket.</div>
                @endforelse
            </div>

            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3">Member Teraktif</h3>
                @forelse ($memberAktif as $m)
                    <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <span class="avatar-circle" style="width:34px;height:34px;font-size:.75rem">
                            {{ mb_strtoupper(mb_substr($m->nama, 0, 1)) }}
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate small">{{ $m->nama }}</div>
                            <div class="text-muted" style="font-size:.74rem">{{ $m->transaksis_count }} transaksi</div>
                        </div>
                        <span class="badge rounded-pill text-bg-light border">{{ $m->poin }} poin</span>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">Belum ada aktivitas member.</div>
                @endforelse
            </div>
        </div>

        {{-- Info untuk admin --}}
        @if ($user->isAdmin())
            <div class="col-12">
                <div class="card-soft p-3 p-lg-4">
                    <h3 class="h6 fw-bold mb-3">Ringkasan Sistem</h3>
                    <div class="row g-3 text-center">
                        @foreach ([['Outlet', $totalOutlet, 'bi-shop', 'outlet.index'], ['User', $totalUser, 'bi-person-gear', 'user.index'], ['Member', $totalMember, 'bi-person-vcard', 'member.index'], ['Paket', $totalPaket, 'bi-tags', 'paket.index']] as [$label, $value, $icon, $route])
                            <div class="col-6 col-lg-3">
                                <a href="{{ route($route) }}" class="d-block p-3 rounded-3 text-decoration-none"
                                   style="background:var(--sky-50);border:1px solid var(--app-border);transition:.2s"
                                   onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <i class="bi {{ $icon }} fs-3 text-sky"></i>
                                    <div class="fs-4 fw-bold">{{ number_format($value, 0, ',', '.') }}</div>
                                    <div class="small text-muted">{{ $label }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
            const grid = () => isDark() ? 'rgba(148,197,255,.12)' : 'rgba(14,165,233,.10)';
            const tick = () => isDark() ? '#8fb6d1' : '#6b8aa8';

            const common = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: tick(), font: { family: 'Plus Jakarta Sans' } } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y ?? ctx.parsed)
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: tick() }, grid: { display: false } },
                    y: {
                        ticks: { color: tick(), callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) },
                        grid: { color: grid() }
                    }
                }
            };

            new Chart(document.getElementById('grafikPendapatan'), {
                type: 'line',
                data: {
                    labels: @json($grafik->pluck('label')),
                    datasets: [{
                        label: 'Pendapatan',
                        data: @json($grafik->pluck('nilai')->map(fn ($v) => (float) $v)),
                        borderColor: '#0ea5e9',
                        backgroundColor: (ctx) => {
                            const {chart} = ctx;
                            const {ctx: c, chartArea} = chart;
                            if (!chartArea) return 'rgba(14,165,233,.2)';
                            const grad = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            grad.addColorStop(0, 'rgba(14,165,233,.45)');
                            grad.addColorStop(1, 'rgba(14,165,233,0)');
                            return grad;
                        },
                        fill: true,
                        tension: .4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0284c7',
                        pointBorderWidth: 2
                    }]
                },
                options: common
            });

            new Chart(document.getElementById('grafikStatus'), {
                type: 'doughnut',
                data: {
                    labels: @json($grafikStatus->pluck('status')),
                    datasets: [{
                        data: @json($grafikStatus->pluck('nilai')),
                        backgroundColor: ['#38bdf8', '#0ea5e9', '#22c55e'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { color: tick(), usePointStyle: true, padding: 16 } } }
                }
            });
        });
    </script>
@endpush