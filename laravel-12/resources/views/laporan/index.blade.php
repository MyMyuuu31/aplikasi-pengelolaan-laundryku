@extends('layouts.app')

@section('title', 'Ringkasan Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Ringkasan performa laundry pada periode terpilih')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-3 no-print">
        @foreach ([
            ['Laporan Transaksi', 'laporan.transaksi', 'bi-journal-text'],
            ['Laporan Pendapatan', 'laporan.pendapatan', 'bi-cash-coin'],
            ['Laporan Paket', 'laporan.paket', 'bi-box-seam'],
            ['Laporan Member', 'laporan.member', 'bi-people'],
        ] as [$label, $route, $icon])
            <a href="{{ route($route, request()->only('dari', 'sampai', 'id_outlet')) }}"
               class="btn {{ request()->routeIs($route) ? 'btn-sky' : 'btn-outline-sky' }}">
                <i class="bi {{ $icon }}"></i> {{ $label }}
            </a>
        @endforeach
    </div>

    @include('partials.filter', [
        'action' => route('laporan.index'),
        'fields' => [
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
            ['name' => 'id_outlet', 'label' => 'Outlet', 'type' => 'select', 'options' => $outlets->pluck('nama_outlet', 'id_outlet')->all()],
        ],
    ])

    <div class="row g-3 mb-3 stagger">
        @foreach ([
            ['Jumlah Transaksi', number_format($ringkasan['jumlah_transaksi'], 0, ',', '.'), 'bi-receipt'],
            ['Pendapatan Kotor', 'Rp '.number_format($ringkasan['pendapatan_kotor'], 0, ',', '.'), 'bi-cash-stack'],
            ['Total Diskon', 'Rp '.number_format($ringkasan['total_diskon'], 0, ',', '.'), 'bi-percent'],
            ['Total Pajak', 'Rp '.number_format($ringkasan['total_pajak'], 0, ',', '.'), 'bi-bank'],
            ['Total Diterima', 'Rp '.number_format($ringkasan['total_diterima'], 0, ',', '.'), 'bi-wallet2'],
        ] as $c)
            <div class="col-6 col-lg">
                <div class="card-soft p-3 h-100">
                    <i class="bi {{ $c[2] }} text-sky fs-5"></i>
                    <div class="small text-muted mt-1">{{ $c[0] }}</div>
                    <div class="fw-bold fs-5">{{ $c[1] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3">Pendapatan per Tanggal</h3>
                <div class="table-responsive">
                    <table class="table table-sky">
                        <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-end">Total Diterima</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($perHari as $r)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="text-center">{{ $r->jml }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format((float) $r->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card-soft p-3 p-lg-4 mb-3">
                <h3 class="h6 fw-bold mb-3">Status Laundry</h3>
                @forelse ($perStatus as $r)
                    @php $meta = config('laundry.transaksi.status.'.$r->status) @endphp
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="badge text-bg-{{ $meta['color'] ?? 'secondary' }}">{{ $meta['label'] ?? $r->status }}</span>
                        <div class="text-end">
                            <div class="fw-bold">{{ $r->jml }} transaksi</div>
                            <div class="small text-muted">Rp {{ number_format((float) $r->total, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">Belum ada data status.</div>
                @endforelse
            </div>

            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3">Grafik Pendapatan</h3>
                <div style="height:240px"><canvas id="grafikLaporan"></canvas></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('grafikLaporan');
            if (!el) return;

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: @json($perHari->map(fn ($r) => \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M'))),
                    datasets: [{
                        label: 'Total Diterima',
                        data: @json($perHari->pluck('total')->map(fn ($v) => (float) $v)),
                        backgroundColor: 'rgba(14,165,233,.65)',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', {notation: 'compact'}).format(v) } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
@endpush