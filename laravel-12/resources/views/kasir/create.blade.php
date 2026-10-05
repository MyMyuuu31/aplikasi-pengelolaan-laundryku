@extends('layouts.app')

@section('title', 'Tambah Kasir')
@section('page-title', 'Tambah Kasir')
@section('page-subtitle', 'Buat akun untuk staf toko Anda')

@section('content')
    <form method="POST" action="{{ route('kasir.store') }}" class="row g-3">
        @csrf
        <div class="col-12 col-xl-8">
            <div class="card-soft p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama kasir</label>
                        <input class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password awal</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card-soft p-4">
                <h2 class="h6 fw-bold">UID login</h2>
                <p class="text-muted small">UID unik dibuat otomatis setelah akun disimpan. Berikan UID dan password kepada kasir.</p>
                <button class="btn btn-sky w-100" type="submit"><i class="bi bi-person-plus"></i> Buat akun kasir</button>
                <a class="btn btn-outline-sky w-100 mt-2" href="{{ route('kasir.index') }}">Batal</a>
            </div>
        </div>
    </form>
@endsection