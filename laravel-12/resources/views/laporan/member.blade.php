@extends('layouts.app')

@section('title', 'Laporan Member')
@section('page-title', 'Laporan Member')
@section('page-subtitle', 'Rekap aktivitas dan nilai belanja pelanggan')

@section('content')
    @include('partials.filter', [
        'action' => route('laporan.member'),
        'fields' => [
            ['name' => 'q', 'label' => 'Kata Kunci', 'placeholder' => 'Nama / kode member'],
        ],
        'exportUrl' => route('laporan.member.export', request()->only('q')),
    ])

    <div class="card-soft p-3">
        <div class="table-responsive">
            <table class="table table-sky align-middle" id="table-member-laporan">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th class="text-center">Transaksi</th>
                    <th class="text-center">Poin</th>
                    <th class="text-end">Total Belanja</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($members as $m)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $m->kode_member }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-circle" style="width:32px;height:32px;font-size:.72rem">{{ mb_strtoupper(mb_substr($m->nama, 0, 1)) }}</span>
                                {{ $m->nama }}
                            </div>
                        </td>
                        <td class="small text-muted">{{ $m->no_hp ?? '-' }}</td>
                        <td class="text-center">{{ $m->transaksis_count }}</td>
                        <td class="text-center"><span class="badge rounded-pill text-bg-light border">{{ $m->poin }}</span></td>
                        <td class="text-end fw-bold">Rp {{ number_format((float) $m->total_belanja, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-person-vcard fs-1 d-block mb-2"></i>Belum ada data member.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="mt-3">{{ $members->links() }}</div>
        @endif
    </div>
@endsection