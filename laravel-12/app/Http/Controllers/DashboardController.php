<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Outlet;
use App\Models\Paket;
use App\Models\PengajuanToko;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $outlets = Outlet::with('owner')->orderBy('nama_outlet')->get();
            $totalOutlet = $outlets->count();
            $totalOwner = User::where('role', 'owner')->count();
            $pengajuanMenunggu = PengajuanToko::where('status', 'menunggu')->count();

            return view('dashboard-admin', compact('outlets', 'totalOutlet', 'totalOwner', 'pengajuanMenunggu'));
        }

        $outletId = $user->id_outlet;
        abort_unless($outletId, 403);
        $bulanIni = now()->startOfMonth();

        $base = Transaksi::query()->when($outletId, fn ($q) => $q->where('id_outlet', $outletId));

        $totalPendapatan = (float) (clone $base)
            ->where('created_at', '>=', $bulanIni)
            ->where('status', 'diambil')
            ->sum('total_bayar');

        $totalTransaksi = (clone $base)->where('created_at', '>=', $bulanIni)->count();
        $transaksiProses = (clone $base)->where('status', 'proses')->count();
        $transaksiSiapDiambil = (clone $base)->where('status', 'selesai')->count();
        $totalMember = Member::query()->when($outletId, fn ($q) => $q->where('id_outlet', $outletId))->count();
        $totalPaket = Paket::query()->where('id_outlet', $outletId)->where('status', 'aktif')->count();
        $totalKasir = User::where('id_outlet', $outletId)->where('role', 'kasir')->where('is_active', true)->count();

        // Grafik pendapatan 7 hari terakhir
        $grafik = collect(range(6, 0))->map(function ($i) use ($base) {
            $tanggal = now()->subDays($i);

            $nilai = (float) (clone $base)
                ->whereDate('created_at', $tanggal->toDateString())
                ->where('status', 'diambil')
                ->sum('total_bayar');

            return [
                'label' => $tanggal->translatedFormat('d M'),
                'nilai' => $nilai,
            ];
        });

        // Grafik status laundry bulan ini
        $grafikStatus = collect(config('laundry.transaksi.status'))
            ->map(function ($meta, $key) use ($base, $bulanIni) {
                return [
                    'status' => $meta['label'],
                    'nilai' => (clone $base)->where('status', $key)->where('created_at', '>=', $bulanIni)->count(),
                ];
            })
            ->values();

        // Paket terlaris bulan ini
        $paketTerlaris = DB::table('tb_detail_transaksi as d')
            ->join('tb_transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->where('t.created_at', '>=', $bulanIni)
            ->when($outletId, fn ($q) => $q->where('t.id_outlet', $outletId))
            ->select('d.nama_paket', 'd.satuan', DB::raw('SUM(d.qty) as total_qty'), DB::raw('SUM(d.subtotal) as total_nilai'))
            ->groupBy('d.nama_paket', 'd.satuan')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Transaksi terbaru
        $transaksiTerbaru = (clone $base)
            ->with(['member', 'user'])
            ->latest()
            ->limit(8)
            ->get();

        // Member paling aktif
        $memberAktif = Member::query()
            ->when($outletId, fn ($q) => $q->where('id_outlet', $outletId))
            ->withCount('transaksis')
            ->withSum('transaksis as total_belanja', 'total_bayar')
            ->has('transaksis')
            ->orderByDesc('transaksis_count')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'transaksiProses',
            'transaksiSiapDiambil',
            'totalMember',
            'totalPaket',
            'totalKasir',
            'grafik',
            'grafikStatus',
            'paketTerlaris',
            'transaksiTerbaru',
            'memberAktif',
        ));
    }
}