<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerOutletController extends Controller
{
    public function edit(Request $request): View
    {
        return view('toko.edit', ['outlet' => $this->outletMilikOwner($request)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $outlet = $this->outletMilikOwner($request);
        $data = $request->validate([
            'nama_outlet' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $outlet->update($data);
        $this->logAktivitas('UBAH', "Memperbarui profil toko {$outlet->nama_outlet}", 'toko');

        return back()->with('success', 'Profil toko berhasil diperbarui.');
    }

    private function outletMilikOwner(Request $request): Outlet
    {
        abort_unless($request->user()->isOwner() && $request->user()->outlet, 403);

        return $request->user()->outlet;
    }
}