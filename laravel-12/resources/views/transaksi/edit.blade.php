@extends('layouts.app')

@section('title', 'Ubah Transaksi')
@section('page-subtitle', $transaksi->no_invoice)

@section('content')
    <form method="POST" action="{{ route('transaksi.update', $transaksi) }}">
        @csrf
        @method('PUT')

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Mengubah transaksi akan mengoreksi nominal dan poin member terkait. Pastikan data sudah benar.
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="card-soft p-3 p-lg-4 mb-3">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-basket text-sky"></i> Item Laundry</h3>

                    <div class="table-responsive">
                        <table class="table table-sky align-middle">
                            <thead>
                            <tr>
                                <th>Paket / Layanan</th>
                                <th class="text-end" style="width:130px">Harga</th>
                                <th style="width:110px">Qty</th>
                                <th class="text-end" style="width:150px">Subtotal</th>
                                <th style="width:60px"></th>
                            </tr>
                            </thead>
                            <tbody id="barisItem">
                            @foreach ($transaksi->details as $i => $d)
                                <tr>
                                    <td>
                                        <select class="form-select form-select-sm" name="items[{{ $i }}][id_paket]">
                                            @foreach ($pakets as $p)
                                                <option value="{{ $p->id_paket }}" @selected($p->id_paket == $d->id_paket)>
                                                    {{ $p->nama_paket }} ({{ $p->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format((float) $d->harga_satuan, 0, ',', '.') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" name="items[{{ $i }}][qty]"
                                               value="{{ (float) $d->qty }}" min="0.01" step="0.01">
                                    </td>
                                    <td class="text-end fw-bold">Rp {{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        @if ($i > 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-soft p-3 p-lg-4">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-calculator text-sky"></i> Diskon & Pajak</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="diskon_persen">Diskon (%)</label>
                            <input type="number" class="form-control" id="diskon_persen" name="diskon_persen" min="0" max="100"
                                   step="0.5" value="{{ old('diskon_persen', $diskonPersen) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pajak_persen">Pajak {{ config('laundry.pajak.nama') }} (%)</label>
                            <input type="number" class="form-control" id="pajak_persen" name="pajak_persen" min="0" max="100"
                                   step="0.5" value="{{ old('pajak_persen', $pajakPersen) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card-soft p-3 p-lg-4 mb-3">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-info-circle text-sky"></i> Info Transaksi</h3>

                    <label class="form-label" for="id_member">Member</label>
                    <select class="form-select" id="id_member" name="id_member">
                        <option value="">Pelanggan Umum</option>
                        @foreach ($members as $m)
                            <option value="{{ $m->id_member }}" @selected($m->id_member == $transaksi->id_member)>
                                {{ $m->kode_member }} - {{ $m->nama }}
                            </option>
                        @endforeach
                    </select>

                    <label class="form-label mt-3" for="metode_bayar">Metode Pembayaran</label>
                    <select class="form-select" id="metode_bayar" name="metode_bayar">
                        @foreach (config('laundry.transaksi.metode_bayar') as $m)
                            <option value="{{ $m }}" @selected($transaksi->metode_bayar === $m)>{{ ucfirst($m) === 'Qris' ? 'QRIS' : ucfirst($m) }}</option>
                        @endforeach
                    </select>

                    <label class="form-label mt-3" for="status">Status Laundry</label>
                    <select class="form-select" id="status" name="status">
                        @foreach (config('laundry.transaksi.status') as $key => $meta)
                            <option value="{{ $key }}" @selected($transaksi->status === $key)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>

                    <label class="form-label mt-3" for="uang_diterima">Uang Diterima (Rp)</label>
                    <input type="number" class="form-control" id="uang_diterima" name="uang_diterima" min="0" step="100"
                           value="{{ old('uang_diterima', (int) ceil((float) $transaksi->uang_diterima ?: $transaksi->total_bayar)) }}">

                    <label class="form-label mt-3" for="catatan">Catatan</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="2">{{ old('catatan', $transaksi->catatan) }}</textarea>
                </div>

                <div class="card-soft p-3 p-lg-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Saat Ini</span>
                        <b class="fs-5 text-sky">Rp {{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</b>
                    </div>
                    <button class="btn btn-sky w-100"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                    <a href="{{ route('transaksi.show', $transaksi) }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection