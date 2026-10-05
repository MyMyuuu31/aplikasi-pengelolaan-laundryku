<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Paket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaketController extends Controller
{
    public function index(Request $request): View
    {
        $outletId = $this->outletAktif();
        abort_unless($outletId, 403);

        $pakets = Paket::with('outlet')->where('id_outlet', $outletId)
            ->when($request->filled('q'), fn ($q) => $q->where('nama_paket', 'like', '%'.$request->q.'%'))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->jenis))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('nama_paket')
            ->paginate(10)
            ->withQueryString();

        return view('paket.index', compact('pakets'));
    }

    public function create(): View
    {
        $outletId = $this->outletAktif();
        abort_unless($outletId, 403);

        return view('paket.create', [
            'paket' => new Paket(['status' => 'aktif', 'satuan' => 'kg', 'jenis' => 'reguler', 'estimasi_hari' => 3]),
            'outlets' => Outlet::where('id_outlet', $outletId)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiPaket($request);
        $data['id_outlet'] = $this->outletIdWajib();
        $paket = Paket::create($data);

        $this->logAktivitas('TAMBAH', "Menambah paket/service {$paket->nama_paket}", 'paket');

        return redirect()->route('paket.index')->with('success', "Paket {$paket->nama_paket} berhasil ditambahkan.");
    }

    public function edit(Paket $paket): View
    {
        $outletId = $this->outletIdWajib();
        abort_unless($paket->id_outlet === $outletId, 404);

        return view('paket.edit', [
            'paket' => $paket,
            'outlets' => Outlet::where('id_outlet', $outletId)->get(),
        ]);
    }

    public function update(Request $request, Paket $paket): RedirectResponse
    {
        abort_unless($paket->id_outlet === $this->outletIdWajib(), 404);
        $data = $this->validasiPaket($request);
        $data['id_outlet'] = $paket->id_outlet;
        $paket->update($data);

        $this->logAktivitas('UBAH', "Mengubah paket/service {$paket->nama_paket}", 'paket');

        return redirect()->route('paket.index')->with('success', "Paket {$paket->nama_paket} berhasil diperbarui.");
    }

    public function destroy(Paket $paket): RedirectResponse
    {
        abort_unless($paket->id_outlet === $this->outletIdWajib(), 404);

        if ($paket->detailTransaksis()->exists()) {
            $paket->update(['status' => 'nonaktif']);
            $this->logAktivitas('ARSIP', "Menonaktifkan jasa {$paket->nama_paket} yang memiliki riwayat transaksi", 'paket');

            return back()->with('success', 'Jasa diarsipkan. Riwayat transaksi tetap tersimpan.');
        }

        $nama = $paket->nama_paket;
        $paket->delete();

        $this->logAktivitas('HAPUS', "Menghapus paket/service {$nama}", 'paket');

        return back()->with('success', "Paket {$nama} berhasil dihapus.");
    }

    public function apiPaketAktif(Request $request): JsonResponse
    {
        $outletId = $request->user()->id_outlet;
        abort_unless($outletId, 403);

        $pakets = Paket::query()
            ->where('status', 'aktif')
            ->where('id_outlet', $outletId)
            ->orderBy('nama_paket')
            ->get(['id_paket', 'nama_paket', 'jenis', 'satuan', 'harga', 'estimasi_hari']);

        return response()->json($pakets);
    }

    private function validasiPaket(Request $request): array
    {
        return $request->validate([
            'nama_paket' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(['reguler', 'kilat', 'setrika', 'satuan', 'khusus'])],
            'satuan' => ['required', Rule::in(['kg', 'pcs', 'item'])],
            'harga' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'estimasi_hari' => ['required', 'integer', 'min:1', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'id_outlet' => ['nullable', 'exists:tb_outlet,id_outlet'],
        ], [
            'nama_paket.required' => 'Nama paket wajib diisi.',
            'jenis.required' => 'Jenis paket wajib dipilih.',
            'satuan.required' => 'Satuan wajib dipilih.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'estimasi_hari.required' => 'Estimasi hari wajib diisi.',
            'estimasi_hari.min' => 'Estimasi minimal 1 hari.',
            'status.required' => 'Status wajib dipilih.',
        ]);
    }

    private function outletIdWajib(): int
    {
        $outletId = $this->outletAktif();
        abort_unless($outletId, 403);

        return (int) $outletId;
    }
}