<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('outlet')->where('role', 'owner')
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($x) => $x->where('nama', 'like', $keyword)
                    ->orWhere('username', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('user.index', compact('users'));
    }

    public function create(): View
    {
        return view('user.create', [
            'user' => new User,
            'outlets' => Outlet::whereDoesntHave('owner')->orderBy('nama_outlet')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiUser($request);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'owner';

        $user = User::create($data);

        $this->logAktivitas('TAMBAH', "Menambah user {$user->nama} ({$user->roleLabel()})", 'user');

        return redirect()->route('user.index')->with('success', "User {$user->nama} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        abort_unless($user->isOwner(), 404);

        return view('user.edit', [
            'user' => $user,
            'outlets' => Outlet::whereDoesntHave('owner', fn ($query) => $query->where('id_user', '!=', $user->id_user))
                ->orderBy('nama_outlet')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isOwner(), 404);

        $data = $this->validasiUser($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['role'] = 'owner';

        $user->update($data);

        $this->logAktivitas('UBAH', "Mengubah data user {$user->nama}", 'user');

        return redirect()->route('user.index')->with('success', "User {$user->nama} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isOwner(), 404);

        if ($user->id_outlet) {
            return back()->with('error', 'Hapus toko melalui menu Toko Laundry agar owner, kasir, dan jasa ditangani bersama.');
        }

        if ($user->id_user === $request->user()->id_user) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $nama = $user->nama;
        $user->delete();

        $this->logAktivitas('HAPUS', "Menghapus user {$nama}", 'user');

        return back()->with('success', "User {$nama} berhasil dihapus.");
    }

    private function validasiUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('tb_user', 'username')->ignore($user?->id_user, 'id_user')],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('tb_user', 'email')->ignore($user?->id_user, 'id_user')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'id_outlet' => ['required', 'exists:tb_outlet,id_outlet'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, titik, garis.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'id_outlet.exists' => 'Outlet tidak ditemukan.',
        ]);

        if (User::query()->where('role', 'owner')->where('id_outlet', $data['id_outlet'])
            ->when($user, fn ($query) => $query->where('id_user', '!=', $user->id_user))
            ->exists()) {
            throw ValidationException::withMessages([
                'id_outlet' => 'Toko ini sudah memiliki owner.',
            ]);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}