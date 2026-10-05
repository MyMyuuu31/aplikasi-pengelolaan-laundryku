@extends('layouts.app')

@section('title', 'Profil Toko')
@section('page-title', 'Profil Toko')
@section('page-subtitle', 'Informasi yang tampil untuk pelanggan toko Anda')

@section('content')
    <form method="POST" action="{{ route('toko.update') }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-12 col-xl-8">
            <div class="card-soft p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="nama_outlet">Nama toko</label>
                        <input class="form-control @error('nama_outlet') is-invalid @enderror" id="nama_outlet" name="nama_outlet" value="{{ old('nama_outlet', $outlet->nama_outlet) }}" required>
                        @error('nama_outlet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="deskripsi">Deskripsi toko dan layanan</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" maxlength="2000">{{ old('deskripsi', $outlet->deskripsi) }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $outlet->alamat) }}</textarea>
                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="telepon">Telepon</label>
                        <input class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $outlet->telepon) }}">
                        @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $outlet->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card-soft p-4">
                <h2 class="h6 fw-bold">Toko Anda</h2>
                <p class="text-muted small">{{ $outlet->nama_outlet }}</p>
                <button class="btn btn-sky w-100" type="submit"><i class="bi bi-check-lg"></i> Simpan profil</button>
            </div>
        </div>
    </form>
@endsection