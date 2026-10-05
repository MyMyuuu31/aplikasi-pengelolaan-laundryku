@extends('layouts.app')

@section('title', 'Data Transaksi')
@section('page-title', 'Data Transaksi')
@section('page-subtitle', 'Riwayat transaksi laundry')

@section('content')
    @include('partials.page-header', [
        'createUrl' => route('transaksi.create'),
        'createLabel' => 'Entri Laundry Baru',
        'subtitle' => 'Total '.number_format($statistik['total'], 0, ',', '.').' transaksi ditemukan',
    ])

    <div class="row g-3 mb-3 stagger">
        @foreach ([
            ['Total Transaksi', number_format($statistik['total'], 0, ',', '.'), 'text-sky'],
            ['Total Nilai', 'Rp '.number_format($statistik['nilai'], 0, ',', '.'), 'text-success'],
            ['Diproses', number_format($statistik['proses'], 0, ',', '.'), 'text-warning'],
            ['Siap Diambil', number_format($statistik['selesai'], 0, ',', '.'), 'text-info'],
            ['Sudah Diambil', number_format($statistik['diambil'], 0, ',', '.'), 'text-primary'],
        ] as $c)
            <div class="col-6 col-lg">
                <div class="card-soft p-3 h-100">
                    <div class="small text-muted">{{ $c[0] }}</div>
                    <div class="fw-bold {{ $c[2] }}">{{ $c[1] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @include('partials.filter', [
        'action' => route('transaksi.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'No invoice / member'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                'proses' => 'Sedang Diproses', 'selesai' => 'Siap Diambil', 'diambil' => 'Sudah Diambil',
            ]],
            ['name' => 'metode_bayar', 'label' => 'Metode Bayar', 'type' => 'select', 'options' => [
                'tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS',
            ]],
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
        ],
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-transaksi">
                <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Member</th>
                    <th>Kasir</th>
                    <th>Status</th>
                    <th>Bayar</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transaksis as $t)
                    <tr>
                        <td class="fw-bold">{{ $t->no_invoice }}</td>
                        <td class="small text-muted">{{ $t->created_at->translatedFormat('d/m/Y H:i') }}</td>
                        <td>{{ $t->namaPelanggan() }}</td>
                        <td class="small">{{ $t->user?->nama ?? '-' }}</td>
                        <td><span class="badge text-bg-{{ $t->statusColor() }}">{{ $t->statusLabel() }}</span></td>
                        <td class="text-uppercase small">{{ $t->metode_bayar }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('transaksi.show', $t) }}" class="btn btn-sm btn-outline-sky" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('transaksi.edit', $t) }}" class="btn btn-sm btn-outline-sky" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-sky" title="Ubah status" data-bs-toggle="modal"
                                    data-bs-target="#modalStatus{{ $t->id_transaksi }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalStatus{{ $t->id_transaksi }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <form method="POST" action="{{ route('transaksi.status', $t) }}">
                                @csrf @method('PATCH')
                                <div class="modal-content" style="background:var(--app-surface);color:var(--app-text)">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">{{ $t->no_invoice }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Status laundry</label>
                                        <select class="form-select" name="status">
                                            @foreach (config('laundry.transaksi.status') as $key => $meta)
                                                <option value="{{ $key }}" @selected($t->status === $key)>{{ $meta['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-sky btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-sky btn-sm">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            Belum ada transaksi. Klik <strong>Entri Laundry Baru</strong> untuk membuat transaksi.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaksis->hasPages())
            <div class="mt-3">{{ $transaksis->links() }}</div>
        @endif
    </div>
@endsection