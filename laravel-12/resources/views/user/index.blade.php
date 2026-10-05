@extends('layouts.app')

@section('title', 'Akun Owner')
@section('page-title', 'Akun Owner')
@section('page-subtitle', 'Kelola pemilik toko laundry')

@section('content')
    @include('partials.page-header', ['createUrl' => route('user.create'), 'createLabel' => 'Tambah Owner'])

    @include('partials.filter', [
        'action' => route('user.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'Nama / username / email'],
        ],
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-user">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Outlet</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-circle" style="width:34px;height:34px;font-size:.75rem">{{ $u->initials() }}</span>
                                <div>
                                    <div class="fw-bold">{{ $u->nama }}</div>
                                    <div class="text-muted" style="font-size:.74rem">{{ $u->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->username }}</td>
                        <td class="small text-muted">{{ $u->outlet?->nama_outlet ?? 'Semua Outlet' }}</td>
                        <td><span class="badge text-bg-info">Owner</span></td>
                        <td>
                            @if ($u->is_active)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('user.edit', $u) }}" class="btn btn-sm btn-outline-sky"><i class="bi bi-pencil"></i></a>
                            @if ($u->id_user !== auth()->id() && ! $u->id_outlet)
                                <form method="POST" action="{{ route('user.destroy', $u) }}" class="d-inline"
                                      data-confirm="Yakin menghapus user {{ $u->nama }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-person-gear fs-1 d-block mb-2"></i> Belum ada data user.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="mt-3">{{ $users->links() }}</div>
        @endif
    </div>
@endsection