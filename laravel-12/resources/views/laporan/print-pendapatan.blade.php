<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan Laundry</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: 12px; color: #0f2942; }
        .kop { border-bottom: 3px solid #0ea5e9; padding-bottom: 10px; margin-bottom: 14px; }
        .kop h1 { font-size: 20px; font-weight: 700; color: #0369a1; margin: 0; }
        .kop p { margin: 0; font-size: 11px; color: #6b8aa8; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; }
        th { background: #e0f2fe; color: #075985; text-align: left; font-size: 11px; text-transform: uppercase; }
        tfoot td { background: #f0f9ff; font-weight: 700; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        h3 { font-size: 13px; font-weight: 700; color: #0369a1; margin: 0 0 6px; }
        .ttd { margin-top: 40px; width: 260px; text-align: center; }
    </style>
</head>
<body>
<div class="toolbar no-print text-end mb-3">
    <button class="btn btn-sm btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<div class="kop d-flex justify-content-between align-items-end">
    <div>
        <h1>LAPORAN PENDAPATAN LAUNDRY</h1>
        <p>{{ config('laundry.app.nama_aplikasi') }} &mdash; {{ config('laundry.app.tagline') }}</p>
    </div>
    <div class="text-end" style="font-size:11px">
        <div><b>Periode</b>: {{ date('d/m/Y', strtotime($dari)) }} s/d {{ date('d/m/Y', strtotime($sampai)) }}</div>
        <div><b>Dicetak</b>: {{ now()->translatedFormat('d F Y H:i') }}</div>
        <div><b>Oleh</b>: {{ auth()->user()->nama }} ({{ auth()->user()->roleLabel() }})</div>
    </div>
</div>

<div class="mb-3" style="font-size:14px">
    <b>Total Pendapatan</b>: Rp {{ number_format($total, 0, ',', '.') }}
</div>

<h3>A. Rekap Harian</h3>
<table>
    <thead>
    <tr>
        <th>Tanggal</th>
        <th class="text-center">Transaksi</th>
        <th class="text-end">Total Harga</th>
        <th class="text-end">Diskon</th>
        <th class="text-end">Pajak</th>
        <th class="text-end">Total Diterima</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($rows as $r)
        <tr>
            <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y') }}</td>
            <td class="text-center">{{ $r->jml_transaksi }}</td>
            <td class="text-end">{{ number_format((float) $r->total_harga, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $r->diskon, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $r->pajak, 0, ',', '.') }}</td>
            <td class="text-end"><b>{{ number_format((float) $r->total_bayar, 0, ',', '.') }}</b></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
    @if ($rows->isNotEmpty())
        <tfoot>
        <tr>
            <td class="text-end">TOTAL</td>
            <td class="text-center">{{ $rows->sum('jml_transaksi') }}</td>
            <td class="text-end">{{ number_format((float) $rows->sum('total_harga'), 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $rows->sum('diskon'), 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $rows->sum('pajak'), 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float) $rows->sum('total_bayar'), 0, ',', '.') }}</td>
        </tr>
        </tfoot>
    @endif
</table>

<h3>B. Pendapatan per Outlet</h3>
<table>
    <thead>
    <tr><th>Outlet</th><th class="text-center">Transaksi</th><th class="text-end">Total Diterima</th></tr>
    </thead>
    <tbody>
    @forelse ($perOutlet as $r)
        <tr><td>{{ $r->outlet }}</td><td class="text-center">{{ $r->jml_transaksi }}</td><td class="text-end">{{ number_format((float) $r->total_bayar, 0, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

<h3>C. Pendapatan per Metode Pembayaran</h3>
<table>
    <thead>
    <tr><th>Metode</th><th class="text-center">Transaksi</th><th class="text-end">Total Diterima</th></tr>
    </thead>
    <tbody>
    @forelse ($perMetode as $r)
        <tr><td class="text-uppercase">{{ $r->metode_bayar }}</td><td class="text-center">{{ $r->jml_transaksi }}</td><td class="text-end">{{ number_format((float) $r->total_bayar, 0, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="ttd">
    <div>{{ config('laundry.app.kota') }}, {{ now()->translatedFormat('d F Y') }}</div>
    <div style="margin-top:60px">Mengetahui,</div>
    <div style="margin-top:55px;font-weight:700">Pemilik Laundry</div>
</div>
</body>
</html>