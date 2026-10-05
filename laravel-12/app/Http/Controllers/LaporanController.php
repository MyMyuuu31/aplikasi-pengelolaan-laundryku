<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Outlet;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);

        $base = Transaksi::query()
            ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('id_outlet', $outletId));

        $ringkasan = [
            'jumlah_transaksi' => (clone $base)->count(),
            'pendapatan_kotor' => (float) (clone $base)->sum('total_harga'),
            'total_diskon' => (float) (clone $base)->sum('diskon'),
            'total_pajak' => (float) (clone $base)->sum('pajak'),
            'total_diterima' => (float) (clone $base)->sum('total_bayar'),
        ];

        $perHari = (clone $base)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jml, SUM(total_bayar) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $perStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as jml, SUM(total_bayar) as total')
            ->groupBy('status')
            ->get();

        return view('laporan.index', [
            'dari' => $dari,
            'sampai' => $sampai,
            'outletId' => $outletId,
            'outlets' => $this->outletsFor($request),
            'ringkasan' => $ringkasan,
            'perHari' => $perHari,
            'perStatus' => $perStatus,
        ]);
    }

    public function transaksi(Request $request): View
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);
        $status = $request->filled('status') ? $request->status : null;

        $transaksis = $this->baseTransaksi($dari, $sampai, $outletId, $status)
            ->with(['member', 'user', 'outlet'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $total = (float) $this->baseTransaksi($dari, $sampai, $outletId, $status)->sum('total_bayar');

        return view('laporan.transaksi', [
            'transaksis' => $transaksis,
            'dari' => $dari,
            'sampai' => $sampai,
            'outletId' => $outletId,
            'status' => $status,
            'outlets' => $this->outletsFor($request),
            'total' => $total,
        ]);
    }

    public function cetakTransaksi(Request $request): View
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);
        $status = $request->filled('status') ? $request->status : null;

        $transaksis = $this->baseTransaksi($dari, $sampai, $outletId, $status)
            ->with(['member', 'user', 'outlet'])
            ->orderBy('created_at')
            ->get();

        $total = (float) $transaksis->sum('total_bayar');

        $this->logAktivitas('CETAK', "Mencetak laporan transaksi periode {$dari} s/d {$sampai}", 'laporan');

        return view('laporan.print-transaksi', compact('transaksis', 'dari', 'sampai', 'total'));
    }

    public function exportTransaksi(Request $request): StreamedResponse
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);
        $status = $request->filled('status') ? $request->status : null;

        $transaksis = $this->baseTransaksi($dari, $sampai, $outletId, $status)
            ->with(['member', 'user', 'outlet'])
            ->orderBy('created_at')
            ->get();

        $this->logAktivitas('EXPORT', "Export laporan transaksi periode {$dari} s/d {$sampai} ({$transaksis->count()} data)", 'laporan');

        return $this->csv('laporan-transaksi-'.now()->format('Ymd-His').'.csv', [
            'No Invoice', 'Tanggal', 'Outlet', 'Kasir', 'Member', 'Total Harga', 'Diskon', 'Pajak', 'Total Bayar', 'Metode', 'Status',
        ], $transaksis->map(fn (Transaksi $t) => [
            $t->no_invoice,
            $t->created_at->format('d/m/Y H:i'),
            $t->outlet?->nama_outlet ?? '-',
            $t->user?->nama ?? '-',
            $t->namaPelanggan(),
            $t->total_harga,
            $t->diskon,
            $t->pajak,
            $t->total_bayar,
            $t->metode_bayar,
            $t->statusLabel(),
        ])->all());
    }

    public function pendapatan(Request $request): View
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);

        $rows = DB::table('tb_transaksi as t')
            ->when($outletId, fn ($q) => $q->where('t.id_outlet', $outletId))
            ->whereBetween('t.created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->selectRaw('DATE(t.created_at) as tanggal, COUNT(*) as jml_transaksi,
                SUM(t.total_harga) as total_harga, SUM(t.diskon) as diskon, SUM(t.pajak) as pajak, SUM(t.total_bayar) as total_bayar')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $perOutlet = DB::table('tb_transaksi as t')
            ->when($outletId, fn ($q) => $q->where('t.id_outlet', $outletId))
            ->whereBetween('t.created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->leftJoin('tb_outlet as o', 'o.id_outlet', '=', 't.id_outlet')
            ->selectRaw("COALESCE(o.nama_outlet, '-') as outlet, COUNT(*) as jml_transaksi, SUM(t.total_bayar) as total_bayar")
            ->groupBy('outlet')
            ->orderByDesc('total_bayar')
            ->get();

        $perMetode = DB::table('tb_transaksi as t')
            ->when($outletId, fn ($q) => $q->where('t.id_outlet', $outletId))
            ->whereBetween('t.created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->selectRaw('metode_bayar, COUNT(*) as jml_transaksi, SUM(t.total_bayar) as total_bayar')
            ->groupBy('metode_bayar')
            ->get();

        $total = (float) $rows->sum('total_bayar');

        return view('laporan.pendapatan', [
            'rows' => $rows,
            'perOutlet' => $perOutlet,
            'perMetode' => $perMetode,
            'dari' => $dari,
            'sampai' => $sampai,
            'outletId' => $outletId,
            'outlets' => $this->outletsFor($request),
            'total' => $total,
        ]);
    }

    public function cetakPendapatan(Request $request): View
    {
        $data = $this->pendapatan($request)->getData();

        $this->logAktivitas('CETAK', "Mencetak laporan pendapatan periode {$request->dari} s/d {$request->sampai}", 'laporan');

        return view('laporan.print-pendapatan', $data);
    }

    public function exportPendapatan(Request $request): StreamedResponse
    {
        $rows = $this->pendapatan($request)->getData()['rows'];

        $this->logAktivitas('EXPORT', "Export laporan pendapatan periode {$request->dari} s/d {$request->sampai}", 'laporan');

        return $this->csv('laporan-pendapatan-'.now()->format('Ymd-His').'.csv', [
            'Tanggal', 'Jumlah Transaksi', 'Total Harga', 'Diskon', 'Pajak', 'Total Diterima',
        ], $rows->map(fn ($r) => [
            $r->tanggal,
            $r->jml_transaksi,
            $r->total_harga,
            $r->diskon,
            $r->pajak,
            $r->total_bayar,
        ])->all());
    }

    public function paket(Request $request): View
    {
        $dari = $request->filled('dari') ? $request->dari : now()->startOfMonth()->toDateString();
        $sampai = $request->filled('sampai') ? $request->sampai : now()->endOfMonth()->toDateString();
        $outletId = $this->outletFilter($request);

        $rows = DB::table('tb_detail_transaksi as d')
            ->join('tb_transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->when($outletId, fn ($q) => $q->where('t.id_outlet', $outletId))
            ->whereBetween('t.created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->selectRaw('d.id_paket, d.nama_paket, d.satuan, SUM(d.qty) as total_qty,
                SUM(d.subtotal) as total_nilai, COUNT(DISTINCT d.id_transaksi) as jml_transaksi')
            ->groupBy('d.id_paket', 'd.nama_paket', 'd.satuan')
            ->orderByDesc('total_qty')
            ->get();

        return view('laporan.paket', [
            'rows' => $rows,
            'dari' => $dari,
            'sampai' => $sampai,
            'outletId' => $outletId,
            'outlets' => $this->outletsFor($request),
            'totalNilai' => (float) $rows->sum('total_nilai'),
        ]);
    }

    public function exportPaket(Request $request): StreamedResponse
    {
        $rows = $this->paket($request)->getData()['rows'];

        $this->logAktivitas('EXPORT', "Export laporan paket periode {$request->dari} s/d {$request->sampai}", 'laporan');

        return $this->csv('laporan-paket-'.now()->format('Ymd-His').'.csv', [
            'Paket', 'Satuan', 'Total Qty', 'Jumlah Transaksi', 'Total Nilai',
        ], $rows->map(fn ($r) => [
            $r->nama_paket, $r->satuan, $r->total_qty, $r->jml_transaksi, $r->total_nilai,
        ])->all());
    }

    public function member(Request $request): View
    {
        $q = $request->filled('q') ? $request->q : null;

        $members = Member::query()
            ->where('id_outlet', $this->outletIdMilikUser($request))
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('nama', 'like', '%'.$q.'%')->orWhere('kode_member', 'like', '%'.$q.'%')))
            ->withCount('transaksis')
            ->withSum('transaksis as total_belanja', 'total_bayar')
            ->orderByDesc('total_belanja')
            ->orderByDesc('transaksis_count')
            ->paginate(15)
            ->withQueryString();

        return view('laporan.member', ['members' => $members, 'q' => $q]);
    }

    public function exportMember(Request $request): StreamedResponse
    {
        $q = $request->filled('q') ? $request->q : null;

        $members = Member::query()
            ->where('id_outlet', $this->outletIdMilikUser($request))
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('nama', 'like', '%'.$q.'%')->orWhere('kode_member', 'like', '%'.$q.'%')))
            ->withCount('transaksis')
            ->withSum('transaksis as total_belanja', 'total_bayar')
            ->orderByDesc('total_belanja')
            ->get();

        $this->logAktivitas('EXPORT', 'Export laporan member ('.$members->count().' data)', 'laporan');

        return $this->csv('laporan-member-'.now()->format('Ymd-His').'.csv', [
            'Kode Member', 'Nama', 'No HP', 'Status', 'Jumlah Transaksi', 'Total Belanja', 'Poin',
        ], $members->map(fn ($m) => [
            $m->kode_member, $m->nama, $m->no_hp, $m->status, $m->transaksis_count, $m->total_belanja, $m->poin,
        ])->all());
    }

    private function baseTransaksi(string $dari, string $sampai, ?int $outletId, ?string $status)
    {
        return Transaksi::query()
            ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('id_outlet', $outletId))
            ->when($status, fn ($q) => $q->where('status', $status));
    }

    private function outletFilter(Request $request): ?int
    {
        if ($request->user()->isAdmin()) {
            return $request->filled('id_outlet') ? (int) $request->id_outlet : null;
        }

        return $this->outletIdMilikUser($request);
    }

    private function outletIdMilikUser(Request $request): int
    {
        $outletId = $request->user()->id_outlet;
        abort_unless($outletId, 403);

        return (int) $outletId;
    }

    private function outletsFor(Request $request)
    {
        return Outlet::query()
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->where('id_outlet', $this->outletIdMilikUser($request)))
            ->orderBy('nama_outlet')
            ->get();
    }

    private function csv(string $filename, array $header, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');

            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}