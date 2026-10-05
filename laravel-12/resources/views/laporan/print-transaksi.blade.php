<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Laundry</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <style>
        @font-face { font-family: bootstrap-icons; src: url("{{ asset('assets/fonts/bootstrap-icons.woff2') }}") format("woff2"); }
        body { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: 12px; color: #244650; }
        .kop { border-bottom: 3px solid #38bdf8; padding-bottom: 10px; margin-bottom: 14px; }
        .kop h1 { font-size: 20px; font-weight: 700; color: #267d99; margin: 0; }
        .kop p { margin: 0; font-size: 11px; color: #687d80; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; }
        th { background: #e0f2fe; color: #075985; text-align: left; font-size: 11px; text-transform: uppercase; }
        tfoot td { background: #f0f9ff; font-weight: 700; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .meta { margin-bottom: 10px; font-size: 11px; }
        .ttd { margin-top: 40px; width: 260px; text-align: center; }
        .toolbar { margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="toolbar no-print text-end">
    <button class="btn btn-sm btn-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak / Simpan PDF
    </button>
</div>

<div class="kop d-flex justify-content-between align-items-end">
    <div>
        <h1>LAPORAN TRANSAKSI LAUNDRY</h1>
        <p>{{ config('laundry.app.nama_aplikasi') }} &mdash; {{ config('laundry.app.tagline') }}</p>
    </div>
    <div class="text-end" style="font-size:11px">
        <div><b>Periode</b>: {{ date('d/m/Y', strtotime($dari)) }} s/d {{ date('d/m/Y', strtotime($sampai)) }}</div>
        <div><b>Dicetak</b>: {{ now()->translatedFormat('d F Y H:i') }}</div>
        <div><b>Oleh</b>: {{ auth()->user()->nama }} ({{ auth()->user()->roleLabel() }})</div>
    </div>
</div>

<div class="meta">
    <b>Total Transaksi</b>: {{ $transaksis->count() }} &nbsp;|&nbsp;
    <b>Total Nilai</b>: Rp {{ number_format($total, 0, ',', '.') }}
</div>

<table>
    <thead>
    <tr>
        <th style="width:35px">No</th>
        <th>No. Invoice</th>
        <th>Tanggal</th>
        <th>Member</th>
        <th>Kasir</th>
        <th>Status</th>
        <th>Bayar</th>
        <th class="text-end">Total Harga</th>
        <th class="text-end">Diskon</th>
        <th class="text-end">Pajak</th>
        <th class="text-end">Total Bayar</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($transaksis as $i => $t)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $t->no_invoice }}</td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $t->namaPelanggan() }}</td>
            <td>{{ $t->user?->nama ?? '-' }}</td>
            <td>{{ $t->statusLabel() }}</td>
            <td class="text-uppercase">{{ $t->metode_bayar }}</td>
            <td class="text-end">{{ number_format((float) $t->total_harga, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $t->diskon, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $t->pajak, 0, ',', '.') }}</td>
            <td class="text-end"><b>{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</b></td>
        </tr>
    @empty
        <tr><td colspan="11" class="text-center">Tidak ada data pada periode ini.</td></tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="7" class="text-end">TOTAL</td>
        <td class="text-end">{{ number_format((float) $transaksis->sum('total_harga'), 0, ',', '.') }}</td>
        <td class="text-end">{{ number_format((float) $transaksis->sum('diskon'), 0, ',', '.') }}</td>
        <td class="text-end">{{ number_format((float) $transaksis->sum('pajak'), 0, ',', '.') }}</td>
        <td class="text-end">{{ number_format((float) $transaksis->sum('total_bayar'), 0, ',', '.') }}</td>
    </tr>
    </tfoot>
</table>

<div class="ttd">
    <div>{{ config('laundry.app.kota') }}, {{ now()->translatedFormat('d F Y') }}</div>
    <div style="margin-top:60px">Mengetahui,</div>
    <div style="margin-top:55px;font-weight:700">Pemilik Laundry</div>
</div>
</body>
</html>