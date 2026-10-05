@extends('layouts.app')

@section('title', 'Edit Kasir')
@section('page-title', 'Edit Kasir')
@section('page-subtitle', $kasir->nama)

@section('content')
    <form method="POST" action="{{ route('kasir.update', $kasir) }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-12 col-xl-8">
            <div class="card-soft p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama kasir</label>
                        <input class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $kasir->nama) }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $kasir->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                        <div class="form-text">Kosongkan jika tidak diganti.</div>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi password baru</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card-soft p-4">
                <label class="form-label" for="uid">UID login</label>
                <input class="form-control mb-3" id="uid" value="{{ $kasir->username }}" readonly>
                <input type="hidden" name="is_active" value="0">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $kasir->is_active))>
                    <label class="form-check-label" for="is_active">Akun aktif</label>
                </div>
                <button class="btn btn-sky w-100" type="submit"><i class="bi bi-check-lg"></i> Simpan perubahan</button>
                <a class="btn btn-outline-sky w-100 mt-2" href="{{ route('kasir.index') }}">Batal</a>
            </div>
        </div>
    </form>
@endsection