<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $logs = $this->query($request)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('log.index', [
            'logs' => $logs,
            'statistik' => [
                'login_gagal' => LogAktivitas::where('aksi', 'LOGIN')->where('deskripsi', 'like', '%Gagal%')->count(),
                'total' => LogAktivitas::count(),
                'modul' => LogAktivitas::select('modul', DB::raw('COUNT(*) as jml'))->groupBy('modul')->pluck('jml', 'modul'),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $logs = $this->query($request)->latest()->get();

        $this->logAktivitas('EXPORT', 'Export log aktivitas ('.$logs->count().' data)', 'log');

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Waktu', 'User', 'Role', 'Modul', 'Aksi', 'Deskripsi', 'IP Address'], ';');

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->nama_user,
                    $log->role,
                    $log->modul,
                    $log->aksi,
                    $log->deskripsi,
                    $log->ip_address,
                ], ';');
            }

            fclose($out);
        }, 'log-aktivitas-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function query(Request $request)
    {
        return LogAktivitas::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($x) => $x->where('nama_user', 'like', $keyword)
                    ->orWhere('deskripsi', 'like', $keyword)
                    ->orWhere('ip_address', 'like', $keyword));
            })
            ->when($request->filled('aksi'), fn ($q) => $q->where('aksi', $request->aksi))
            ->when($request->filled('modul'), fn ($q) => $q->where('modul', $request->modul))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('created_at', '<=', $request->sampai));
    }
}