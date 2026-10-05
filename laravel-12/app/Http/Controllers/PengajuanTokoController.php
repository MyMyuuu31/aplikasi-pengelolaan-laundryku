<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\PengajuanToko;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengajuanTokoController extends Controller
{
    public function create(): View
    {
        return view('toko.daftar');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_owner' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:tb_user,username', 'unique:tb_pengajuan_toko,username'],
            'email' => ['required', 'email', 'max:100', 'unique:tb_user,email', 'unique:tb_pengajuan_toko,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nama_outlet' => ['required', 'string', 'max:100', 'unique:tb_outlet,nama_outlet', 'unique:tb_pengajuan_toko,nama_outlet'],
            'alamat' => ['required', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['password'] = Hash::make($data['password']);
        PengajuanToko::create($data);

        return redirect()->route('login')->with('success', 'Pengajuan toko terkirim. Akun dapat digunakan setelah disetujui admin.');
    }

    public function index(): View
    {
        $pengajuans = PengajuanToko::with(['owner', 'admin'])
            ->latest()
            ->paginate(15);

        return view('pengajuan.index', compact('pengajuans'));
    }

    public function approve(Request $request, PengajuanToko $pengajuan): RedirectResponse
    {
        $ownerName = $pengajuan->nama_owner;

        DB::transaction(function () use ($request, $pengajuan): void {
            $pengajuan = PengajuanToko::whereKey($pengajuan->id_pengajuan)->lockForUpdate()->firstOrFail();
            abort_unless($pengajuan->status === 'menunggu', 422, 'Pengajuan ini sudah diproses.');

            if (User::where('username', $pengajuan->username)->exists()
                || User::where('email', $pengajuan->email)->exists()
                || Outlet::where('nama_outlet', $pengajuan->nama_outlet)->exists()) {
                abort(422, 'Username, email, atau nama toko sudah digunakan.');
            }

            $outlet = Outlet::create([
                'nama_outlet' => $pengajuan->nama_outlet,
                'alamat' => $pengajuan->alamat,
                'telepon' => $pengajuan->telepon,
                'deskripsi' => $pengajuan->deskripsi,
            ]);

            $owner = User::create([
                'nama' => $pengajuan->nama_owner,
                'username' => $pengajuan->username,
                'email' => $pengajuan->email,
                'password' => $pengajuan->password,
                'role' => 'owner',
                'id_outlet' => $outlet->id_outlet,
                'is_active' => true,
            ]);

            $pengajuan->update([
                'status' => 'disetujui',
                'id_owner' => $owner->id_user,
                'id_admin' => $request->user()->id_user,
            ]);
        });

        $this->logAktivitas('SETUJU', "Menyetujui pendaftaran toko untuk {$ownerName}", 'pengajuan_toko');

        return back()->with('success', 'Pengajuan disetujui. Akun owner dan toko berhasil dibuat.');
    }

    public function reject(Request $request, PengajuanToko $pengajuan): RedirectResponse
    {
        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'id_admin' => $request->user()->id_user,
        ]);
        $this->logAktivitas('TOLAK', "Menolak pendaftaran toko {$pengajuan->nama_outlet}", 'pengajuan_toko');

        return back()->with('success', 'Pengajuan toko ditolak.');
    }
}