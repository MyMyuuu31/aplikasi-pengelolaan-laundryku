@extends('layouts.app')

@section('title', 'Data Paket')
@section('page-title', 'Data Paket / Service')
@section('page-subtitle', 'Katalog layanan laundry dan harga')

@section('content')
    @include('partials.page-header', ['createUrl' => route('paket.create'), 'createLabel' => 'Tambah Paket'])

    @include('partials.filter', [
        'action' => route('paket.index'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'Nama paket'],
            ['name' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'options' => [
                'reguler' => 'Reguler', 'kilat' => 'Kilat', 'setrika' => 'Setrika', 'satuan' => 'Satuan', 'khusus' => 'Khusus',
            ]],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']],
        ],
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-paket">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama Paket</th>
                    <th>Jenis</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga</th>
                    <th class="text-center">Estimasi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($pakets as $p)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold">{{ $p->nama_paket }}</div>
                            @if ($p->deskripsi)
                                <div class="text-muted small text-truncate" style="max-width:280px">{{ $p->deskripsi }}</div>
                            @endif
                        </td>
                        <td><span class="badge rounded-pill text-bg-light border">{{ ucfirst($p->jenis) }}</span></td>
                        <td>{{ $p->satuan }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format((float) $p->harga, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $p->estimasi_hari }} hari</td>
                        <td>
                            @if ($p->status === 'aktif')
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('paket.edit', $p) }}" class="btn btn-sm btn-outline-sky"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('paket.destroy', $p) }}" class="d-inline"
                                  data-confirm="Yakin menghapus paket {{ $p->nama_paket }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-tags fs-1 d-block mb-2"></i>
                            Belum ada data paket. Klik <strong>Tambah Paket</strong> untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($pakets->hasPages())
            <div class="mt-3">{{ $pakets->links() }}</div>
        @endif
    </div>
@endsection