@extends('layouts.app')

@section('title', 'Data Member')
@section('page-title', 'Data Member')
@section('page-subtitle', 'Kelola pelanggan terdaftar')

@section('content')
    @include('partials.page-header', ['createUrl' => route('member.create'), 'createLabel' => 'Tambah Member'])

    @include('partials.filter', [
        'action' => route('member.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'Nama / kode / no HP'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']],
        ],
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-member">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Kode</th>
                    <th>Nama Member</th>
                    <th>Kontak</th>
                    <th class="text-center">Poin</th>
                    <th class="text-center">Transaksi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($members as $m)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $m->kode_member }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-circle" style="width:34px;height:34px;font-size:.75rem">{{ mb_strtoupper(mb_substr($m->nama, 0, 1)) }}</span>
                                <div>
                                    <div class="fw-bold">{{ $m->nama }}</div>
                                    <div class="text-muted" style="font-size:.74rem">{{ $m->outlet?->nama_outlet ?? 'Semua Outlet' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="small">
                            @if ($m->no_hp)<i class="bi bi-telephone"></i> {{ $m->no_hp }}<br>@endif
                            @if ($m->email)<i class="bi bi-envelope"></i> {{ $m->email }}@endif
                            @if (!$m->no_hp && !$m->email)<span class="text-muted">-</span>@endif
                        </td>
                        <td class="text-center"><span class="badge rounded-pill text-bg-light border">{{ $m->poin }}</span></td>
                        <td class="text-center">{{ $m->transaksis_count }}</td>
                        <td>
                            @if ($m->status === 'aktif')
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('member.edit', $m) }}" class="btn btn-sm btn-outline-sky"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('member.destroy', $m) }}" class="d-inline"
                                  data-confirm="Yakin menghapus member {{ $m->nama }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-person-vcard fs-1 d-block mb-2"></i>
                            Belum ada data member. Klik <strong>Tambah Member</strong> untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="mt-3">{{ $members->links() }}</div>
        @endif
    </div>
@endsection