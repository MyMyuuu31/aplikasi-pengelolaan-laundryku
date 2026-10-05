@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')
@section('page-subtitle', 'Riwayat aktivitas seluruh pengguna aplikasi')

@section('content')
    <div class="row g-3 mb-3">
        @foreach ([
            ['Total Log', number_format($statistik['total'], 0, ',', '.'), 'bi-clock-history'],
            ['Login Gagal', number_format($statistik['login_gagal'], 0, ',', '.'), 'bi-shield-exclamation'],
        ] as $c)
            <div class="col-6 col-lg-3">
                <div class="card-soft p-3">
                    <i class="bi {{ $c[2] }} text-sky fs-5"></i>
                    <div class="small text-muted">{{ $c[0] }}</div>
                    <div class="fw-bold fs-5">{{ $c[1] }}</div>
                </div>
            </div>
        @endforeach

        @foreach ($statistik['modul'] as $modul => $jml)
            <div class="col-6 col-lg-3">
                <div class="card-soft p-3">
                    <i class="bi bi-folder2 text-sky fs-5"></i>
                    <div class="small text-muted">Modul {{ ucfirst($modul ?? 'umum') }}</div>
                    <div class="fw-bold fs-5">{{ number_format($jml, 0, ',', '.') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @include('partials.filter', [
        'action' => route('log.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'User / deskripsi / IP'],
            ['name' => 'aksi', 'label' => 'Aksi', 'type' => 'select', 'options' => [
                'LOGIN' => 'Login', 'LOGOUT' => 'Logout', 'TAMBAH' => 'Tambah', 'UBAH' => 'Ubah',
                'HAPUS' => 'Hapus', 'LIHAT' => 'Lihat', 'CETAK' => 'Cetak', 'EXPORT' => 'Export',
            ]],
            ['name' => 'modul', 'label' => 'Modul', 'type' => 'select', 'options' => [
                'auth' => 'Autentikasi', 'outlet' => 'Outlet', 'user' => 'User', 'member' => 'Member',
                'paket' => 'Paket', 'transaksi' => 'Transaksi', 'laporan' => 'Laporan', 'log' => 'Log',
            ]],
            ['name' => 'dari', 'label' => 'Dari Tanggal', 'type' => 'date'],
            ['name' => 'sampai', 'label' => 'Sampai Tanggal', 'type' => 'date'],
        ],
        'exportUrl' => route('log.export', request()->only('q', 'aksi', 'modul', 'dari', 'sampai')),
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-log">
                <thead>
                <tr>
                    <th style="width:150px">Waktu</th>
                    <th>User</th>
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="small">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $log->nama_user ?? 'Tanpa Login' }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $log->role ?? '-' }}</div>
                        </td>
                        <td><span class="badge rounded-pill text-bg-light border">{{ ucfirst($log->modul ?? 'umum') }}</span></td>
                        <td>
                            @php
                                $warna = match ($log->aksi) {
                                    'LOGIN' => 'success', 'LOGOUT' => 'secondary',
                                    'TAMBAH' => 'primary', 'UBAH' => 'warning',
                                    'HAPUS' => 'danger', default => 'info',
                                };
                            @endphp
                            <span class="badge text-bg-{{ $warna }}">{{ $log->aksi }}</span>
                        </td>
                        <td class="small">{{ $log->deskripsi }}</td>
                        <td class="small text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-clock-history fs-1 d-block mb-2"></i>Belum ada aktivitas tercatat.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="mt-3">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection