<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(Request $request): View
    {
        $kasirs = User::query()
            ->where('role', 'kasir')
            ->where('id_outlet', $request->user()->id_outlet)
            ->orderBy('nama')
            ->paginate(10);

        return view('kasir.index', compact('kasirs'));
    }

    public function create(): View
    {
        return view('kasir.create', ['kasir' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanOwnerPunyaToko($request);
        $data = $this->validasiKasir($request, true);
        $data['username'] = $this->uidBaru();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'kasir';
        $data['id_outlet'] = $request->user()->id_outlet;
        $data['is_active'] = true;

        $kasir = User::create($data);
        $this->logAktivitas('TAMBAH', "Menambahkan kasir {$kasir->nama} dengan UID {$kasir->username}", 'kasir');

        return redirect()->route('kasir.index')->with('success', "Akun kasir dibuat. UID login: {$kasir->username}");
    }

    public function edit(Request $request, User $kasir): View
    {
        $this->pastikanKasirMilikOwner($request, $kasir);

        return view('kasir.edit', compact('kasir'));
    }

    public function update(Request $request, User $kasir): RedirectResponse
    {
        $this->pastikanKasirMilikOwner($request, $kasir);
        $data = $this->validasiKasir($request, false, $kasir);

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $kasir->update($data);
        $this->logAktivitas('UBAH', "Memperbarui akun kasir {$kasir->username}", 'kasir');

        return redirect()->route('kasir.index')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function destroy(Request $request, User $kasir): RedirectResponse
    {
        $this->pastikanKasirMilikOwner($request, $kasir);
        $uid = $kasir->username;
        $kasir->delete();
        $this->logAktivitas('HAPUS', "Menghapus akun kasir UID {$uid}", 'kasir');

        return back()->with('success', 'Akun kasir berhasil dihapus. Riwayat transaksi tetap tersimpan.');
    }

    private function validasiKasir(Request $request, bool $passwordRequired, ?User $kasir = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('tb_user', 'email')->ignore($kasir?->id_user, 'id_user')],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama kasir wajib diisi.',
            'password.required' => 'Password awal wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
    }

    private function uidBaru(): string
    {
        do {
            $uid = 'KSR-'.Str::upper(Str::random(8));
        } while (User::where('username', $uid)->exists());

        return $uid;
    }

    private function pastikanOwnerPunyaToko(Request $request): void
    {
        abort_unless($request->user()->isOwner() && $request->user()->id_outlet, 403);
    }

    private function pastikanKasirMilikOwner(Request $request, User $kasir): void
    {
        $this->pastikanOwnerPunyaToko($request);
        abort_unless($kasir->isKasir() && $kasir->id_outlet === $request->user()->id_outlet, 404);
    }
}