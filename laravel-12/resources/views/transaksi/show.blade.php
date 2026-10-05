@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')
@section('page-subtitle', $transaksi->no_invoice)

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card-soft p-3 p-lg-4 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold mb-0">{{ $transaksi->no_invoice }}</h3>
                        <small class="text-muted">{{ $transaksi->created_at->translatedFormat('l, d F Y \p\u\k\u\l H:i') }}</small>
                    </div>
                    <span class="badge text-bg-{{ $transaksi->statusColor() }} fs-6">{{ $transaksi->statusLabel() }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sky align-middle">
                        <thead>
                        <tr>
                            <th>Paket</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($transaksi->details as $d)
                            <tr>
                                <td class="fw-semibold">{{ $d->nama_paket }}</td>
                                <td class="text-end">{{ number_format((float) $d->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-center">{{ rtrim(rtrim(number_format((float) $d->qty, 2, ',', '.'), '0'), ',') }} {{ $d->satuan }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-12 col-md-7">
                        <table class="table table-sm">
                            <tbody>
                            <tr>
                                <td class="text-muted">Total Harga</td>
                                <td class="text-end">{{ number_format((float) $transaksi->total_harga, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Diskon ({{ rtrim(rtrim((string) $transaksi->diskon_persen, '0'), '.') }}%)</td>
                                <td class="text-end text-danger">- {{ number_format((float) $transaksi->diskon, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ config('laundry.pajak.nama') }} ({{ rtrim(rtrim((string) $transaksi->pajak_persen, '0'), '.') }}%)</td>
                                <td class="text-end">{{ number_format((float) $transaksi->pajak, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold">Total Bayar</td>
                                <td class="text-end fw-bold fs-5 text-sky">Rp {{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($transaksi->catatan)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Catatan:</strong> {{ $transaksi->catatan }}
                </div>
            @endif
        </div>

        <div class="col-12 col-xl-4">
            <div class="card-soft p-3 p-lg-4 mb-3">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-person-badge text-sky"></i> Informasi</h3>
                <ul class="list-unstyled small d-grid gap-3 mb-0">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Member</span>
                        <b>{{ $transaksi->namaPelanggan() }}</b>
                    </li>
                    @if ($transaksi->member)
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Kode Member</span><b>{{ $transaksi->member->kode_member }}</b>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Poin</span><b>{{ $transaksi->member->poin }}</b>
                        </li>
                    @endif
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Kasir</span><b>{{ $transaksi->user?->nama ?? '-' }}</b>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Outlet</span><b>{{ $transaksi->outlet?->nama_outlet ?? 'Umum' }}</b>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Metode Bayar</span><b class="text-uppercase">{{ $transaksi->metode_bayar }}</b>
                    </li>
                </ul>
            </div>

            <div class="card-soft p-3 p-lg-4 mb-3">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-wallet2 text-sky"></i> Pembayaran</h3>

                @if ((float) $transaksi->uang_diterima > 0)
                    <ul class="list-unstyled small d-grid gap-2 mb-3">
                        <li class="d-flex justify-content-between"><span class="text-muted">Uang diterima</span><b>Rp {{ number_format((float) $transaksi->uang_diterima, 0, ',', '.') }}</b></li>
                        <li class="d-flex justify-content-between"><span class="text-muted">Kembalian</span><b>Rp {{ number_format((float) $transaksi->total_kembali, 0, ',', '.') }}</b></li>
                    </ul>
                @else
                    <form method="POST" action="{{ route('transaksi.kembalian', $transaksi) }}" class="d-grid gap-2">
                        @csrf
                        <label class="form-label mb-0" for="uang_diterima_baru">Uang Diterima (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" min="0" step="100" class="form-control" id="uang_diterima_baru"
                                   name="uang_diterima" value="{{ (int) ceil((float) $transaksi->total_bayar) }}" required>
                            <button class="btn btn-sky"><i class="bi bi-check-lg"></i></button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="card-soft p-3 p-lg-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-arrow-repeat text-sky"></i> Ubah Status</h3>
                <form method="POST" action="{{ route('transaksi.status', $transaksi) }}" class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <select class="form-select" name="status">
                        @foreach (config('laundry.transaksi.status') as $key => $meta)
                            <option value="{{ $key }}" @selected($transaksi->status === $key)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sky"><i class="bi bi-check-lg"></i></button>
                </form>

                <hr>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-sky" onclick="window.open('{{ route('transaksi.show', $transaksi) }}','_blank')">
                        <i class="bi bi-printer"></i> Cetak Struk
                    </button>
                    <a href="{{ route('transaksi.edit', $transaksi) }}" class="btn btn-outline-sky">
                        <i class="bi bi-pencil"></i> Ubah Transaksi
                    </a>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-outline-sky">
                        <i class="bi bi-list"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection