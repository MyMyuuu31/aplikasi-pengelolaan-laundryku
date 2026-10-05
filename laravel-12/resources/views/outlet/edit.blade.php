@extends('layouts.app')

@section('title', 'Ubah Toko')
@section('page-title', 'Ubah Toko')
@section('page-subtitle', $outlet->nama_outlet)

@section('content')
    <form method="POST" action="{{ route('outlet.update', $outlet) }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Informasi Toko</h3>

                <div class="mb-3">
                    <label class="form-label" for="nama_outlet">Nama Outlet <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_outlet') is-invalid @enderror" id="nama_outlet"
                           name="nama_outlet" value="{{ old('nama_outlet', $outlet->nama_outlet) }}">
                    @error('nama_outlet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat"
                              rows="3">{{ old('alamat', $outlet->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="telepon">Telepon</label>
                        <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon"
                               name="telepon" value="{{ old('telepon', $outlet->telepon) }}">
                        @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email', $outlet->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4 mb-3">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-save text-sky"></i> Simpan</h3>
                <button class="btn btn-sky w-100 mb-2"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                <a href="{{ route('outlet.index') }}" class="btn btn-outline-sky w-100">Batal</a>
            </div>

        </div>
    </form>
@endsection