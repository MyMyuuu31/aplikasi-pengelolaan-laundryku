@extends('layouts.app')

@section('title', 'Akun Kasir')
@section('page-title', 'Akun Kasir')
@section('page-subtitle', 'Akun yang dapat mengakses toko Anda')

@section('content')
    @include('partials.page-header', ['createUrl' => route('kasir.create'), 'createLabel' => 'Tambah Kasir'])
    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle mb-0">
                <thead><tr><th>Nama</th><th>UID Login</th><th>Email</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse ($kasirs as $kasir)
                    <tr>
                        <td class="fw-semibold">{{ $kasir->nama }}</td>
                        <td><code>{{ $kasir->username }}</code></td>
                        <td>{{ $kasir->email ?? '-' }}</td>
                        <td><span class="badge text-bg-{{ $kasir->is_active ? 'success' : 'secondary' }}">{{ $kasir->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-sky" href="{{ route('kasir.edit', $kasir) }}" title="Edit kasir"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('kasir.destroy', $kasir) }}" data-confirm="Hapus akun kasir {{ $kasir->nama }}? Riwayat transaksi tetap disimpan.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus kasir"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted py-5" colspan="5">Belum ada akun kasir untuk toko ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($kasirs->hasPages()) <div class="mt-3">{{ $kasirs->links() }}</div> @endif
    </div>
@endsection