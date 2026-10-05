<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Paket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $outlets = Outlet::with('owner')
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($x) => $x->where('nama_outlet', 'like', $keyword)
                    ->orWhere('alamat', 'like', $keyword)
                    ->orWhere('telepon', 'like', $keyword));
            })
            ->orderBy('nama_outlet')
            ->paginate(10)
            ->withQueryString();

        return view('outlet.index', compact('outlets'));
    }

    public function create(): View
    {
        return view('outlet.create', ['outlet' => new Outlet]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiOutlet($request);

        $outlet = Outlet::create($data);

        $this->logAktivitas('TAMBAH', "Menambah outlet {$outlet->nama_outlet}", 'outlet');

        return redirect()->route('outlet.index')->with('success', "Outlet {$outlet->nama_outlet} berhasil ditambahkan.");
    }

    public function edit(Outlet $outlet): View
    {
        return view('outlet.edit', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $data = $this->validasiOutlet($request);

        $outlet->update($data);

        $this->logAktivitas('UBAH', "Mengubah data outlet {$outlet->nama_outlet}", 'outlet');

        return redirect()->route('outlet.index')->with('success', "Outlet {$outlet->nama_outlet} berhasil diperbarui.");
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $nama = $outlet->nama_outlet;

        DB::transaction(function () use ($outlet): void {
            User::where('id_outlet', $outlet->id_outlet)
                ->whereIn('role', ['owner', 'kasir'])
                ->delete();

            Paket::where('id_outlet', $outlet->id_outlet)->update(['status' => 'nonaktif']);

            $outlet->delete();
        });

        $this->logAktivitas('HAPUS', "Menghapus outlet {$nama}", 'outlet');

        return back()->with('success', "Outlet {$nama} berhasil dihapus.");
    }

    private function validasiOutlet(Request $request): array
    {
        return $request->validate([
            'nama_outlet' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
        ], [
            'nama_outlet.required' => 'Nama outlet wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);
    }
}