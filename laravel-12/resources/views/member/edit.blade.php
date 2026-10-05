@extends('layouts.app')

@section('title', 'Ubah Member')
@section('page-title', 'Ubah Member')
@section('page-subtitle', $member->nama)

@section('content')
    <form method="POST" action="{{ route('member.update', $member) }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-person-vcard text-sky"></i> Data Member</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="kode_member">Kode Member</label>
                        <input type="text" class="form-control @error('kode_member') is-invalid @enderror" id="kode_member"
                               name="kode_member" value="{{ old('kode_member', $member->kode_member) }}">
                        @error('kode_member') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                               name="nama" value="{{ old('nama', $member->nama) }}">
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_hp">Nomor HP</label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp"
                               name="no_hp" value="{{ old('no_hp', $member->no_hp) }}">
                        @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email', $member->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2">{{ old('alamat', $member->alamat) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="poin">Poin</label>
                        <input type="number" min="0" class="form-control" id="poin" name="poin" value="{{ old('poin', $member->poin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status">
                            <option value="aktif" @selected(old('status', $member->status) === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status', $member->status) === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4 mb-3">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Outlet</h3>
                <select class="form-select" id="id_outlet" name="id_outlet">
                    <option value="">Semua Outlet</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id_outlet }}" @selected(old('id_outlet', $member->id_outlet) == $outlet->id_outlet)>
                            {{ $outlet->nama_outlet }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-sky w-100 mt-4"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                <a href="{{ route('member.index') }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
            </div>

            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-bar-chart text-sky"></i> Statistik</h3>
                <ul class="list-unstyled small d-grid gap-2 mb-0">
                    <li class="d-flex justify-content-between"><span class="text-muted">Jumlah transaksi</span><b>{{ $member->totalTransaksi() }}</b></li>
                    <li class="d-flex justify-content-between"><span class="text-muted">Total belanja</span><b>Rp {{ number_format($member->totalBelanja(), 0, ',', '.') }}</b></li>
                    <li class="d-flex justify-content-between"><span class="text-muted">Poin</span><b>{{ $member->poin }}</b></li>
                </ul>
            </div>
        </div>
    </form>
@endsection