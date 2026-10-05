@extends('layouts.app')

@section('title', 'Pengajuan Toko')
@section('page-title', 'Pengajuan Toko')
@section('page-subtitle', 'Periksa pendaftaran owner dan toko baru')

@section('content')
    <section class="card-soft p-3 p-lg-4">
        <div class="table-responsive">
            <table class="table table-sky align-middle mb-0">
                <thead><tr><th>Owner</th><th>Toko</th><th>Kontak</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse ($pengajuans as $pengajuan)
                    <tr>
                        <td><div class="fw-semibold">{{ $pengajuan->nama_owner }}</div><div class="small text-muted">{{ $pengajuan->username }}</div></td>
                        <td><div class="fw-semibold">{{ $pengajuan->nama_outlet }}</div><div class="small text-muted">{{ $pengajuan->alamat }}</div></td>
                        <td><div>{{ $pengajuan->email }}</div><div class="small text-muted">{{ $pengajuan->telepon ?? '-' }}</div></td>
                        <td>
                            @php($statusClass = ['menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'secondary'][$pengajuan->status])
                            <span class="badge text-bg-{{ $statusClass }}">{{ ucfirst($pengajuan->status) }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($pengajuan->status === 'menunggu')
                                <form method="POST" action="{{ route('pengajuan-toko.approve', $pengajuan) }}" class="d-inline" data-confirm="Setujui pengajuan {{ $pengajuan->nama_outlet }} dan buat akun owner?"><button class="btn btn-sm btn-sky" title="Setujui"><i class="bi bi-check-lg"></i></button>@csrf</form>
                                <form method="POST" action="{{ route('pengajuan-toko.reject', $pengajuan) }}" class="d-inline" data-confirm="Tolak pengajuan {{ $pengajuan->nama_outlet }}?"><button class="btn btn-sm btn-outline-danger" title="Tolak"><i class="bi bi-x-lg"></i></button>@csrf</form>
                            @else
                                <span class="small text-muted">{{ $pengajuan->admin?->nama ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">Belum ada pengajuan toko.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($pengajuans->hasPages()) <div class="mt-3">{{ $pengajuans->links() }}</div> @endif
    </section>
@endsection