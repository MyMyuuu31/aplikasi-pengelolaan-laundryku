<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $outletId = $this->outletIdWajib();
        $members = Member::with('outlet')->where('id_outlet', $outletId)
            ->withCount('transaksis')
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($x) => $x->where('nama', 'like', $keyword)
                    ->orWhere('kode_member', 'like', $keyword)
                    ->orWhere('no_hp', 'like', $keyword));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('id_member')
            ->paginate(10)
            ->withQueryString();

        return view('member.index', compact('members'));
    }

    public function create(): View
    {
        return view('member.create', [
            'member' => new Member(['status' => 'aktif']),
            'outlets' => Outlet::where('id_outlet', $this->outletIdWajib())->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiMember($request);
        $data['kode_member'] = $this->kodeMemberOtomatis();
        $data['id_outlet'] = $this->outletIdWajib();

        $member = Member::create($data);

        $this->logAktivitas('TAMBAH', "Menambah member {$member->kode_member} - {$member->nama}", 'member');

        return redirect()->route('member.index')->with('success', "Member {$member->nama} ({$member->kode_member}) berhasil ditambahkan.");
    }

    public function edit(Member $member): View
    {
        abort_unless($member->id_outlet === $this->outletIdWajib(), 404);

        return view('member.edit', [
            'member' => $member,
            'outlets' => Outlet::where('id_outlet', $member->id_outlet)->get(),
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->id_outlet === $this->outletIdWajib(), 404);
        $data = $this->validasiMember($request, $member);
        $data['id_outlet'] = $member->id_outlet;

        $member->update($data);

        $this->logAktivitas('UBAH', "Mengubah data member {$member->kode_member} - {$member->nama}", 'member');

        return redirect()->route('member.index')->with('success', "Member {$member->nama} berhasil diperbarui.");
    }

    public function destroy(Member $member): RedirectResponse
    {
        abort_unless($member->id_outlet === $this->outletIdWajib(), 404);
        $nama = $member->nama;
        $kode = $member->kode_member;
        $member->delete();

        $this->logAktivitas('HAPUS', "Menghapus member {$kode} - {$nama}", 'member');

        return back()->with('success', "Member {$nama} berhasil dihapus.");
    }

    /**
     * Endpoint pencarian member untuk halaman entri transaksi.
     */
    public function apiCari(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->q);

        $data = Member::query()
            ->where('status', 'aktif')
            ->where('id_outlet', $this->outletIdWajib())
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = '%'.$keyword.'%';
                $q->where(fn ($x) => $x->where('nama', 'like', $like)
                    ->orWhere('kode_member', 'like', $like)
                    ->orWhere('no_hp', 'like', $like));
            })
            ->orderBy('nama')
            ->limit(15)
            ->get(['id_member', 'kode_member', 'nama', 'no_hp', 'poin']);

        return response()->json($data);
    }

    private function kodeMemberOtomatis(): string
    {
        $prefix = 'MBR';
        $terakhir = Member::orderByDesc('id_member')->value('kode_member');
        $urutan = 1;

        if ($terakhir && preg_match('/(\d+)$/', $terakhir, $m)) {
            $urutan = (int) $m[1] + 1;
        }

        do {
            $kode = $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Member::where('kode_member', $kode)->exists());

        return $kode;
    }

    private function validasiMember(Request $request, ?Member $member = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kode_member' => ['nullable', 'string', 'max:20', Rule::unique('tb_member', 'kode_member')->ignore($member?->id_member, 'id_member')],
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'poin' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'id_outlet' => ['nullable', 'exists:tb_outlet,id_outlet'],
        ], [
            'nama.required' => 'Nama member wajib diisi.',
            'no_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, dan tanda + atau -.',
            'email.email' => 'Format email tidak valid.',
            'kode_member.unique' => 'Kode member sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $data['poin'] = (int) ($data['poin'] ?? 0);

        return $data;
    }

    private function outletIdWajib(): int
    {
        $outletId = $this->outletAktif();
        abort_unless($outletId, 403);

        return (int) $outletId;
    }
}