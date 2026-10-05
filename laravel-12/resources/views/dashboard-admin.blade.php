@extends('layouts.app')

@section('title', 'Platform')
@section('page-title', 'Platform')
@section('page-subtitle', 'Kelola toko laundry dan akun owner')

@section('content')
    <section class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-bold text-sky mb-2">Administrasi LaundryKu</p>
            <h2 class="h3 fw-bold mb-1">Toko yang terdaftar</h2>
            <p class="text-muted mb-0">Kelola profil toko dan akun pemiliknya dari satu tempat.</p>
        </div>
        <a href="{{ route('outlet.create') }}" class="btn btn-sky"><i class="bi bi-plus-lg"></i> Tambah toko</a>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card-soft p-3 d-flex align-items-center gap-3">
                <span class="stat-icon"><i class="bi bi-shop"></i></span>
                <div><div class="text-muted small">Toko</div><div class="stat-value">{{ $totalOutlet }}</div></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card-soft p-3 d-flex align-items-center gap-3">
                <span class="stat-icon stat-icon-mint"><i class="bi bi-person-badge"></i></span>
                <div><div class="text-muted small">Akun owner</div><div class="stat-value">{{ $totalOwner }}</div></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <a href="{{ route('pengajuan-toko.index') }}" class="card-soft p-3 d-flex align-items-center gap-3 text-decoration-none h-100">
                <span class="stat-icon"><i class="bi bi-inbox"></i></span>
                <div><div class="text-muted small">Pengajuan menunggu</div><div class="stat-value">{{ $pengajuanMenunggu }}</div></div>
            </a>
        </div>
    </div>

    <section class="card-soft p-3 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 fw-bold mb-0">Daftar toko</h3>
            <a href="{{ route('outlet.index') }}" class="btn btn-sm btn-outline-sky">Semua toko</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sky align-middle mb-0">
                <thead><tr><th>Nama toko</th><th>Owner</th><th>Kontak</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse ($outlets as $outlet)
                    <tr>
                        <td><div class="fw-semibold">{{ $outlet->nama_outlet }}</div><div class="small text-muted">{{ $outlet->alamat ?? 'Alamat belum diisi' }}</div></td>
                        <td>{{ $outlet->owner?->nama ?? 'Belum ditetapkan' }}</td>
                        <td>{{ $outlet->telepon ?? $outlet->email ?? '-' }}</td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-sky" href="{{ route('outlet.edit', $outlet) }}" title="Kelola toko"><i class="bi bi-arrow-up-right"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-5">Belum ada toko terdaftar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection