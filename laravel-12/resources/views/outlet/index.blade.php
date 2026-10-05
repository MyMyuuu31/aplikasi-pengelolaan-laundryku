@extends('layouts.app')

@section('title', 'Toko Laundry')
@section('page-title', 'Toko Laundry')
@section('page-subtitle', 'Kelola toko dan akun owner')

@section('content')
    @include('partials.page-header', ['createUrl' => route('outlet.create'), 'createLabel' => 'Tambah Toko'])

    @include('partials.filter', [
        'action' => route('outlet.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'Nama / alamat / telepon'],
        ],
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-outlet">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama Toko</th>
                    <th>Alamat</th>
                    <th>Kontak</th>
                    <th>Owner</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($outlets as $outlet)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $outlet->nama_outlet }}</td>
                        <td class="text-muted small">{{ $outlet->alamat ?? '-' }}</td>
                        <td class="small">
                            @if ($outlet->telepon)<i class="bi bi-telephone"></i> {{ $outlet->telepon }}<br>@endif
                            @if ($outlet->email)<i class="bi bi-envelope"></i> {{ $outlet->email }}@endif
                            @if (!$outlet->telepon && !$outlet->email)<span class="text-muted">-</span>@endif
                        </td>
                        <td>
                            @if ($outlet->owner)
                                <div class="fw-semibold">{{ $outlet->owner->nama }}</div>
                                <div class="small text-muted">{{ $outlet->owner->username }}</div>
                            @else
                                <span class="badge text-bg-warning">Belum ada owner</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('outlet.edit', $outlet) }}" class="btn btn-sm btn-outline-sky" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('outlet.destroy', $outlet) }}" class="d-inline"
                                  data-confirm="Yakin menghapus outlet {{ $outlet->nama_outlet }}?">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-shop fs-1 d-block mb-2"></i>
                            Belum ada data outlet. Klik <strong>Tambah Outlet</strong> untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($outlets->hasPages())
            <div class="mt-3 no-print">{{ $outlets->links() }}</div>
        @endif
    </div>
@endsection