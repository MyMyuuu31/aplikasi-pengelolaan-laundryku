<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Member;
use App\Models\Paket;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $transaksis = $this->queryDasar($request)
            ->with(['member', 'user', 'outlet'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statistik = [
            'total' => (clone $this->queryDasar($request))->count(),
            'nilai' => (float) (clone $this->queryDasar($request))->sum('total_bayar'),
            'proses' => (clone $this->queryDasar($request))->where('status', 'proses')->count(),
            'selesai' => (clone $this->queryDasar($request))->where('status', 'selesai')->count(),
            'diambil' => (clone $this->queryDasar($request))->where('status', 'diambil')->count(),
        ];

        return view('transaksi.index', compact('transaksis', 'statistik'));
    }

    public function create(): View
    {
        $outletId = $this->outletIdWajib(request());

        return view('transaksi.create', [
            'transaksi' => new Transaksi([
                'metode_bayar' => 'tunai',
                'status' => 'proses',
                'pajak_persen' => config('laundry.pajak.default'),
            ]),
            'pakets' => Paket::where('status', 'aktif')->where('id_outlet', $outletId)
                ->orderBy('nama_paket')
                ->get(),
            'members' => Member::where('status', 'aktif')->where('id_outlet', $outletId)->orderBy('nama')->get(),
            'pajakPersen' => config('laundry.pajak.default'),
            'diskonPersen' => 0,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiTransaksi($request);
        $outletId = $this->outletIdWajib($request);

        $transaksi = DB::transaction(function () use ($data, $request, $outletId) {
            $totalHarga = $data['items'] === [] ? 0 : array_sum(array_column($data['items'], 'subtotal'));
            $diskonPersen = min((float) $data['diskon_persen'], (float) config('laundry.transaksi.diskon_max'));
            $diskon = round($totalHarga * $diskonPersen / 100, 2);
            $pajakPersen = (float) $data['pajak_persen'];
            $pajak = round(($totalHarga - $diskon) * $pajakPersen / 100, 2);
            $totalBayar = $totalHarga - $diskon + $pajak;
            $uangDiterima = (float) $data['uang_diterima'];
            $kembali = max($uangDiterima - $totalBayar, 0);

            $transaksi = Transaksi::create([
                'no_invoice' => $this->noInvoiceOtomatis(),
                'id_outlet' => $outletId,
                'id_member' => $data['id_member'] ?: null,
                'id_user' => $request->user()->id_user,
                'total_harga' => $totalHarga,
                'diskon' => $diskon,
                'diskon_persen' => $diskonPersen,
                'pajak_persen' => $pajakPersen,
                'pajak' => $pajak,
                'total_bayar' => $totalBayar,
                'uang_diterima' => $uangDiterima,
                'total_kembali' => $kembali,
                'metode_bayar' => $data['metode_bayar'],
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                DetailTransaksi::create($item + ['id_transaksi' => $transaksi->id_transaksi]);
            }

            // Tambah poin member 1% dari total belanja (pembulatan ke bawah).
            if ($transaksi->id_member) {
                $member = Member::find($transaksi->id_member);
                if ($member) {
                    $member->increment('poin', (int) floor($totalHarga / 100));
                }
            }

            return $transaksi;
        });

        $this->logAktivitas(
            'TAMBAH',
            "Membuat transaksi {$transaksi->no_invoice} totaling Rp ".number_format((float) $transaksi->total_bayar, 0, ',', '.'),
            'transaksi'
        );

        return redirect()->route('transaksi.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->no_invoice} berhasil disimpan.");
    }

    public function show(Transaksi $transaksi): View
    {
        $this->pastikanTransaksiMilikOutlet(request(), $transaksi);
        $transaksi->load(['details.paket', 'member', 'user', 'outlet']);

        $this->logAktivitas('LIHAT', "Melihat detail transaksi {$transaksi->no_invoice}", 'transaksi');

        return view('transaksi.show', compact('transaksi'));
    }

    public function edit(Transaksi $transaksi): View
    {
        $outletId = $this->outletIdWajib(request());
        $this->pastikanTransaksiMilikOutlet(request(), $transaksi);
        $transaksi->load('details');

        return view('transaksi.edit', [
            'transaksi' => $transaksi,
            'pakets' => Paket::where('status', 'aktif')->where('id_outlet', $outletId)
                ->orderBy('nama_paket')
                ->get(),
            'members' => Member::where('status', 'aktif')->where('id_outlet', $outletId)->orderBy('nama')->get(),
            'pajakPersen' => (float) $transaksi->pajak_persen,
            'diskonPersen' => (float) $transaksi->diskon_persen,
        ]);
    }

    public function update(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->pastikanTransaksiMilikOutlet($request, $transaksi);
        $data = $this->validasiTransaksi($request, $transaksi);

        DB::transaction(function () use ($data, $transaksi) {
            $totalHarga = array_sum(array_column($data['items'], 'subtotal'));
            $diskonPersen = min((float) $data['diskon_persen'], (float) config('laundry.transaksi.diskon_max'));
            $diskon = round($totalHarga * $diskonPersen / 100, 2);
            $pajakPersen = (float) $data['pajak_persen'];
            $pajak = round(($totalHarga - $diskon) * $pajakPersen / 100, 2);
            $totalBayar = $totalHarga - $diskon + $pajak;
            $uangDiterima = (float) $data['uang_diterima'];

            $transaksi->update([
                'id_member' => $data['id_member'] ?: null,
                'total_harga' => $totalHarga,
                'diskon' => $diskon,
                'diskon_persen' => $diskonPersen,
                'pajak_persen' => $pajakPersen,
                'pajak' => $pajak,
                'total_bayar' => $totalBayar,
                'uang_diterima' => $uangDiterima,
                'total_kembali' => max($uangDiterima - $totalBayar, 0),
                'metode_bayar' => $data['metode_bayar'],
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            $transaksi->details()->delete();
            foreach ($data['items'] as $item) {
                DetailTransaksi::create($item + ['id_transaksi' => $transaksi->id_transaksi]);
            }
        });

        $this->logAktivitas('UBAH', "Mengubah transaksi {$transaksi->no_invoice}", 'transaksi');

        return redirect()->route('transaksi.show', $transaksi)->with('success', "Transaksi {$transaksi->no_invoice} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->pastikanTransaksiMilikOutlet($request, $transaksi);
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('laundry.transaksi.status')))],
        ], ['status.required' => 'Status wajib dipilih.']);

        $transaksi->update(['status' => $data['status']]);

        $this->logAktivitas('UBAH', "Mengubah status transaksi {$transaksi->no_invoice} menjadi {$transaksi->statusLabel()}", 'transaksi');

        return back()->with('success', "Status transaksi {$transaksi->no_invoice} berhasil diperbarui.");
    }

    public function kembalian(Request $request, Transaksi $transaksi): JsonResponse
    {
        $this->pastikanTransaksiMilikOutlet($request, $transaksi);
        $data = $request->validate([
            'uang_diterima' => ['required', 'numeric', 'min:0'],
        ], ['uang_diterima.required' => 'Uang diterima wajib diisi.']);

        $totalBayar = (float) $transaksi->total_bayar;
        $diterima = (float) $data['uang_diterima'];
        $kembali = round($diterima - $totalBayar, 2);

        $transaksi->update([
            'uang_diterima' => $diterima,
            'total_kembali' => $kembali,
        ]);

        $this->logAktivitas(
            'UBAH',
            "Mencatat pembayaran transaksi {$transaksi->no_invoice} sebesar Rp ".number_format($diterima, 0, ',', '.').($kembali < 0 ? ' (kurang bayar)' : ''),
            'transaksi'
        );

        return response()->json([
            'total_bayar' => $totalBayar,
            'uang_diterima' => $diterima,
            'total_kembali' => $kembali,
            'kurang_bayar' => $kembali < 0,
        ]);
    }

    private function queryDasar(Request $request)
    {
        $outletId = $this->outletIdWajib($request);

        return Transaksi::query()
            ->where('id_outlet', $outletId)
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($x) => $x->where('no_invoice', 'like', $keyword)
                    ->orWhereHas('member', fn ($m) => $m->where('nama', 'like', $keyword)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('metode_bayar'), fn ($q) => $q->where('metode_bayar', $request->metode_bayar))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('created_at', '<=', $request->sampai));
    }

    private function validasiTransaksi(Request $request, ?Transaksi $transaksi = null): array
    {
        $outletId = $this->outletIdWajib($request);
        $allowedStatuses = array_keys(config('laundry.transaksi.status'));
        if (! $transaksi) {
            $allowedStatuses = array_diff($allowedStatuses, ['dibatalkan']);
        }

        $request->validate([
            'id_member' => ['nullable', 'exists:tb_member,id_member'],
            'uang_diterima' => ['required', 'numeric', 'min:0'],
            'metode_bayar' => ['required', Rule::in(config('laundry.transaksi.metode_bayar'))],
            'status' => ['required', Rule::in($allowedStatuses)],
            'diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pajak_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_paket' => ['required', 'exists:tb_paket,id_paket'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01', 'max:9999'],
        ], [
            'uang_diterima.required' => 'Uang diterima wajib diisi.',
            'items.required' => 'Minimal satu item laundry wajib ditambahkan.',
            'items.min' => 'Minimal satu item laundry wajib ditambahkan.',
            'items.*.id_paket.required' => 'Pilih paket untuk setiap item.',
            'items.*.qty.min' => 'Qty minimal 0,01.',
        ]);

        if ($request->filled('id_member') && ! Member::where('id_member', $request->id_member)->where('id_outlet', $outletId)->exists()) {
            throw ValidationException::withMessages(['id_member' => 'Pelanggan tidak ditemukan di toko ini.']);
        }

        $paketIds = collect($request->input('items'))->pluck('id_paket')->unique();
        $paketTerpilih = Paket::where('status', 'aktif')
            ->where('id_outlet', $outletId)
            ->whereIn('id_paket', $paketIds)
            ->get()
            ->keyBy('id_paket');

        if ($paketTerpilih->count() !== $paketIds->count()) {
            throw ValidationException::withMessages(['items' => 'Paket harus aktif dan berasal dari toko Anda.']);
        }

        $items = collect($request->input('items'))
            ->map(function ($item) use ($paketTerpilih) {
                $paket = $paketTerpilih[$item['id_paket']];
                $qty = (float) $item['qty'];

                return [
                    'id_paket' => $paket->id_paket,
                    'nama_paket' => $paket->nama_paket,
                    'satuan' => $paket->satuan,
                    'harga_satuan' => (float) $paket->harga,
                    'qty' => $qty,
                    'subtotal' => round((float) $paket->harga * $qty, 2),
                ];
            })
            ->values()
            ->all();

        return [
            'id_member' => $request->input('id_member'),
            'uang_diterima' => $request->input('uang_diterima'),
            'metode_bayar' => $request->input('metode_bayar'),
            'status' => $request->input('status'),
            'diskon_persen' => $request->input('diskon_persen', 0),
            'pajak_persen' => $request->input('pajak_persen', 0),
            'catatan' => $request->input('catatan'),
            'items' => $items,
        ];
    }

    private function noInvoiceOtomatis(): string
    {
        $prefix = config('laundry.invoice.prefix').'-'.now()->format('Ymd');
        $remarks = (int) config('laundry.invoice.remarks');

        $terakhir = Transaksi::where('no_invoice', 'like', $prefix.'%')
            ->orderByDesc('no_invoice')
            ->value('no_invoice');

        $urutan = 1;
        if ($terakhir && preg_match('/(\d+)$/', $terakhir, $m)) {
            $urutan = (int) $m[1] + 1;
        }

        do {
            $invoice = $prefix.str_pad((string) $urutan, $remarks, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Transaksi::where('no_invoice', $invoice)->exists());

        return $invoice;
    }

    private function outletIdWajib(Request $request): int
    {
        $outletId = $request->user()->id_outlet;
        abort_unless($outletId, 403);

        return (int) $outletId;
    }

    private function pastikanTransaksiMilikOutlet(Request $request, Transaksi $transaksi): void
    {
        abort_unless($transaksi->id_outlet === $this->outletIdWajib($request), 404);
    }
}