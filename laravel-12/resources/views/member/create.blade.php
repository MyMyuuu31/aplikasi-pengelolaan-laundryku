@extends('layouts.app')

@section('title', 'Tambah Member')
@section('page-title', 'Tambah Member')
@section('page-subtitle', 'Kode member dibuat otomatis oleh sistem')

@section('content')
    <form method="POST" action="{{ route('member.store') }}" class="row g-3">
        @csrf

        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-person-plus text-sky"></i> Data Member</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                               name="nama" value="{{ old('nama') }}">
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="kode_member">Kode Member</label>
                        <input type="text" class="form-control" id="kode_member" name="kode_member"
                               value="{{ old('kode_member') }}" placeholder="Dibuat otomatis (MBR0001)">
                        @error('kode_member') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_hp">Nomor HP</label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp"
                               name="no_hp" value="{{ old('no_hp') }}" placeholder="081234567890">
                        @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="poin">Poin</label>
                        <input type="number" min="0" class="form-control" id="poin" name="poin" value="{{ old('poin', 0) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status">
                            <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Outlet</h3>
                <select class="form-select" id="id_outlet" name="id_outlet">
                    <option value="">Otomatis ({{ auth()->user()->outlet?->nama_outlet ?? 'Semua Outlet' }})</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id_outlet }}" @selected(old('id_outlet') == $outlet->id_outlet)>
                            {{ $outlet->nama_outlet }}
                        </option>
                    @endforeach
                </select>

                <p class="text-muted small mt-3">
                    Member mendapat 1 poin untuk setiap Rp100 total laundry (pembulatan ke bawah).
                </p>

                <button class="btn btn-sky w-100 mt-2"><i class="bi bi-check-lg"></i> Simpan Member</button>
                <a href="{{ route('member.index') }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
@endsection