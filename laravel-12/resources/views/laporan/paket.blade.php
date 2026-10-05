@extends('layouts.app')

@section('title', 'Laporan Paket')
@section('page-title', 'Laporan Paket / Service')
@section('page-subtitle', 'Rekap penjualan per paket laundry')

@section('content')
    @include('partials.filter', [
        'action' => route('laporan.paket'),
        'fields' => [
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
            ['name' => 'id_outlet', 'label' => 'Outlet', 'type' => 'select', 'options' => $outlets->pluck('nama_outlet', 'id_outlet')->all()],
        ],
        'exportUrl' => route('laporan.paket.export', request()->only('dari', 'sampai', 'id_outlet')),
    ])

    <div class="card-soft p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h3 class="h6 fw-bold mb-0">Total Nilai Penjualan Paket</h3>
            <div class="fs-3 fw-bold text-sky">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Paket / Layanan</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Total Qty</th>
                    <th class="text-center">Transaksi</th>
                    <th class="text-end">Total Nilai</th>
                    <th style="width:180px">Proporsi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $i => $r)
                    @php $persen = $totalNilai > 0 ? round((float) $r->total_nilai / $totalNilai * 100, 1) : 0 @endphp
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $r->nama_paket }}</td>
                        <td class="text-center">{{ strtoupper($r->satuan) }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format((float) $r->total_qty, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="text-center">{{ $r->jml_transaksi }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format((float) $r->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar" style="width:{{ $persen }}%;background:linear-gradient(90deg,#38bdf8,#0284c7)"></div>
                                </div>
                                <span class="small text-muted">{{ $persen }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada penjualan paket pada periode ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection