<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftarkan Toko · {{ config('laundry.app.nama_aplikasi') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<main class="login-shell">
    <section class="login-card p-4 p-lg-5" style="max-width:780px">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="small text-uppercase fw-bold text-sky mb-2">LaundryKu</p>
                <h1 class="h3 fw-bold mb-1">Daftarkan toko</h1>
                <p class="text-muted mb-0">Akun owner aktif setelah pengajuan diperiksa admin.</p>
            </div>
            <a href="{{ route('login') }}" class="btn btn-outline-sky" aria-label="Kembali ke login"><i class="bi bi-arrow-left"></i></a>
        </div>

        <form method="POST" action="{{ route('daftar-toko.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label" for="nama_owner">Nama pemilik</label>
                <input class="form-control @error('nama_owner') is-invalid @enderror" id="nama_owner" name="nama_owner" value="{{ old('nama_owner') }}" required>
                @error('nama_owner') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="nama_outlet">Nama toko</label>
                <input class="form-control @error('nama_outlet') is-invalid @enderror" id="nama_outlet" name="nama_outlet" value="{{ old('nama_outlet') }}" required>
                @error('nama_outlet') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="username">Username owner</label>
                <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>
            <div class="col-12">
                <label class="form-label" for="alamat">Alamat toko</label>
                <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2" required>{{ old('alamat') }}</textarea>
                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="deskripsi">Deskripsi dan layanan</label>
                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telepon">Telepon</label>
                <input class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon') }}">
                @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end pt-2">
                <button class="btn btn-sky" type="submit"><i class="bi bi-send"></i> Kirim pengajuan</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>