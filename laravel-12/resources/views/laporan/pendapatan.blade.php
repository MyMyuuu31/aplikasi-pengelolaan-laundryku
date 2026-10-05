@extends('layouts.app')

@section('title', 'Laporan Pendapatan')
@section('page-title', 'Laporan Pendapatan')
@section('page-subtitle', 'Rekap pendapatan harian, per outlet, dan per metode pembayaran')

@section('content')
    @include('partials.filter', [
        'action' => route('laporan.pendapatan'),
        'fields' => [
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
            ['name' => 'id_outlet', 'label' => 'Outlet', 'type' => 'select', 'options' => $outlets->pluck('nama_outlet', 'id_outlet')->all()],
        ],
        'exportUrl' => route('laporan.pendapatan.export', request()->only('dari', 'sampai', 'id_outlet')),
        'printUrl' => route('laporan.pendapatan.cetak', request()->only('dari', 'sampai', 'id_outlet')),
    ])

    <div class="card-soft p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h3 class="h6 fw-bold mb-0">Total Pendapatan Periode Ini</h3>
            <div class="fs-3 fw-bold text-sky">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3">Rekap Harian</h3>
                <div class="table-responsive">
                    <table class="table table-sky">
                        <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-end">Total Harga</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Diterima</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($rows as $r)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="text-center">{{ $r->jml_transaksi }}</td>
                                <td class="text-end">{{ number_format((float) $r->total_harga, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">{{ number_format((float) $r->diskon, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $r->pajak, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $r->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data pendapatan.</td></tr>
                        @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-end">{{ number_format((float) $rows->sum('total_harga'), 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $rows->sum('diskon'), 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $rows->sum('pajak'), 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $rows->sum('total_bayar'), 0, ',', '.') }}</td>
                            </tr>
                            </tfoot>
                        @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card-soft p-3 p-lg-4 mb-3">
                <h3 class="h6 fw-bold mb-3">Per Outlet</h3>
                @forelse ($perOutlet as $r)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="small">{{ $r->outlet }}</span>
                        <div class="text-end">
                            <div class="fw-bold small">Rp {{ number_format((float) $r->total_bayar, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $r->jml_transaksi }} transaksi</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">Belum ada data.</div>
                @endforelse
            </div>

            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3">Per Metode Pembayaran</h3>
                @forelse ($perMetode as $r)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="badge text-bg-light border text-uppercase">{{ $r->metode_bayar }}</span>
                        <div class="text-end">
                            <div class="fw-bold small">Rp {{ number_format((float) $r->total_bayar, 0, ',', '.') }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $r->jml_transaksi }} transaksi</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">Belum ada data.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection