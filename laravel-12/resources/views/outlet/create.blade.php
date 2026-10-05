@extends('layouts.app')

@section('title', 'Tambah Outlet')
@section('page-title', 'Tambah Outlet')
@section('page-subtitle', 'Daftarkan toko untuk akun owner')

@section('content')
    <form method="POST" action="{{ route('outlet.store') }}" class="row g-3">
        @csrf
        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Informasi Outlet</h3>

                <div class="mb-3">
                    <label class="form-label" for="nama_outlet">Nama Outlet <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_outlet') is-invalid @enderror" id="nama_outlet"
                           name="nama_outlet" value="{{ old('nama_outlet') }}" placeholder="Contoh: LaundryKu Pusat">
                    @error('nama_outlet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat"
                              rows="3" placeholder="Alamat lengkap outlet">{{ old('alamat') }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="telepon">Telepon</label>
                        <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon"
                               name="telepon" value="{{ old('telepon') }}" placeholder="021-5550101">
                        @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email') }}" placeholder="outlet@laundryku.test">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-save text-sky"></i> Simpan toko</h3>
                <p class="text-muted small">Setelah toko dibuat, tautkan akun owner melalui menu Akun Owner.</p>
                <button class="btn btn-sky w-100 mb-2"><i class="bi bi-check-lg"></i> Simpan Toko</button>
                <a href="{{ route('outlet.index') }}" class="btn btn-outline-sky w-100">Batal</a>
            </div>
        </div>
    </form>
@endsection