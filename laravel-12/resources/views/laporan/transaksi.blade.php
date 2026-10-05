@extends('layouts.app')

@section('title', 'Laporan Transaksi')
@section('page-title', 'Laporan Transaksi')
@section('page-subtitle', 'Periode '.date('d/m/Y', strtotime($dari)).' s/d '.date('d/m/Y', strtotime($sampai)))

@section('content')
    @include('partials.filter', [
        'action' => route('laporan.transaksi'),
        'fields' => [
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
            ['name' => 'id_outlet', 'label' => 'Outlet', 'type' => 'select', 'options' => $outlets->pluck('nama_outlet', 'id_outlet')->all()],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                'proses' => 'Sedang Diproses', 'selesai' => 'Siap Diambil', 'diambil' => 'Sudah Diambil',
            ]],
        ],
        'exportUrl' => route('laporan.transaksi.export', request()->only('dari', 'sampai', 'id_outlet', 'status')),
        'printUrl' => route('laporan.transaksi.cetak', request()->only('dari', 'sampai', 'id_outlet', 'status')),
    ])

    <div class="card-soft p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="h6 fw-bold mb-0">Daftar Transaksi</h3>
            <span class="badge rounded-pill" style="background:rgba(14,165,233,.12);color:#0284c7;">
                Total Rp {{ number_format($total, 0, ',', '.') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-laporan">
                <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Outlet</th>
                    <th>Member</th>
                    <th>Kasir</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transaksis as $t)
                    <tr>
                        <td class="fw-bold">{{ $t->no_invoice }}</td>
                        <td class="small">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="small">{{ $t->outlet?->nama_outlet ?? '-' }}</td>
                        <td>{{ $t->namaPelanggan() }}</td>
                        <td class="small">{{ $t->user?->nama ?? '-' }}</td>
                        <td><span class="badge text-bg-{{ $t->statusColor() }}">{{ $t->statusLabel() }}</span></td>
                        <td class="text-end fw-bold">Rp {{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaksis->hasPages())
            <div class="mt-3">{{ $transaksis->links() }}</div>
        @endif
    </div>
@endsection