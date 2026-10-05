@extends('layouts.app')

@section('title', 'Tambah Owner')
@section('page-title', 'Tambah Owner')
@section('page-subtitle', 'Buat akun pemilik dan hubungkan ke satu toko')

@section('content')
    <form method="POST" action="{{ route('user.store') }}" class="row g-3">
        @csrf

        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-person-plus text-sky"></i> Data Akun</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                               name="nama" value="{{ old('nama') }}">
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username Owner <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                               name="username" value="{{ old('username') }}">
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                               name="email" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                               name="password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Toko Owner</h3>

                <label class="form-label" for="id_outlet">Toko <span class="text-danger">*</span></label>
                <select class="form-select" id="id_outlet" name="id_outlet">
                    <option value="">Pilih toko yang belum memiliki owner</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id_outlet }}" @selected(old('id_outlet') == $outlet->id_outlet)>
                            {{ $outlet->nama_outlet }}
                        </option>
                    @endforeach
                </select>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Akun aktif</label>
                </div>

                <button class="btn btn-sky w-100 mt-4"><i class="bi bi-check-lg"></i> Buat akun owner</button>
                <a href="{{ route('user.index') }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
@endsection