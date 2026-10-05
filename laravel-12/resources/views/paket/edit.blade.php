@extends('layouts.app')

@section('title', 'Ubah Paket')
@section('page-title', 'Ubah Paket')
@section('page-subtitle', $paket->nama_paket)

@section('content')
    <form method="POST" action="{{ route('paket.update', $paket) }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-12 col-lg-8">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-tags text-sky"></i> Detail Paket</h3>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="nama_paket">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_paket') is-invalid @enderror" id="nama_paket"
                               name="nama_paket" value="{{ old('nama_paket', $paket->nama_paket) }}">
                        @error('nama_paket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jenis">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" id="jenis" name="jenis">
                            @foreach (['reguler', 'kilat', 'setrika', 'satuan', 'khusus'] as $j)
                                <option value="{{ $j }}" @selected(old('jenis', $paket->jenis) === $j)>{{ ucfirst($j) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="satuan">Satuan <span class="text-danger">*</span></label>
                        <select class="form-select" id="satuan" name="satuan">
                            @foreach (['kg', 'pcs', 'item'] as $s)
                                <option value="{{ $s }}" @selected(old('satuan', $paket->satuan) === $s)>{{ strtoupper($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="estimasi_hari">Estimasi (hari) <span class="text-danger">*</span></label>
                        <input type="number" min="1" max="30" class="form-control @error('estimasi_hari') is-invalid @enderror"
                               id="estimasi_hari" name="estimasi_hari" value="{{ old('estimasi_hari', $paket->estimasi_hari) }}">
                        @error('estimasi_hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="harga">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="100" min="0" class="form-control @error('harga') is-invalid @enderror"
                                   id="harga" name="harga" value="{{ old('harga', $paket->harga) }}">
                        </div>
                        @error('harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status">
                            <option value="aktif" @selected(old('status', $paket->status) === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status', $paket->status) === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-soft p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shop text-sky"></i> Outlet</h3>
                <select class="form-select" id="id_outlet" name="id_outlet">
                    <option value="">Semua Outlet (umum)</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id_outlet }}" @selected(old('id_outlet', $paket->id_outlet) == $outlet->id_outlet)>
                            {{ $outlet->nama_outlet }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-sky w-100 mt-4"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                <a href="{{ route('paket.index') }}" class="btn btn-outline-sky w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
@endsection