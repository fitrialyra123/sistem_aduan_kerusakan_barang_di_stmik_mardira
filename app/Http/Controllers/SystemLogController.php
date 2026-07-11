<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SystemLog::with('user');

        // Filter berdasarkan user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan method HTTP
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter berdasarkan aksi
        if ($request->filled('aksi')) {
            $query->where('aksi', 'like', '%' . $request->aksi . '%');
        }

        // Filter berdasarkan URL
        if ($request->filled('url')) {
            $query->where('url', 'like', '%' . $request->url . '%');
        }

        // ===== FILTER BERDASARKAN STATUS / ERROR =====
        if ($request->filled('status')) {
            if ($request->status === 'errors') {
                $query->where('is_error', true);
            } else {
                $query->where('status_code', $request->status);
            }
        }

        // Filter berdasarkan range tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Menghapus paginate() dan menggantinya dengan get() untuk DataTables
        $logs = $query->orderBy('created_at', 'desc')->get();

        // [TAMBAHAN UNTUK DATATABLES]
        // Jika DataTables meminta data via AJAX, kembalikan dalam format JSON
        if ($request->ajax()) {
            return response()->json(['data' => $logs]);
        }

        $users = User::all();
        $totalErrors = SystemLog::where('is_error', true)->count();

        $filters = array_merge(
            [
                'user_id' => null, 'method' => null, 'aksi' => null, 
                'url' => null, 'status' => null, 'date_from' => null, 'date_to' => null,
            ],
            $request->only(['user_id', 'method', 'aksi', 'url', 'status', 'date_from', 'date_to'])
        );

        return view('logs.index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $filters,
            'totalErrors' => $totalErrors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(SystemLog $systemLog)
    {
        // Parameter diubah menyesuaikan Route Model Binding dari Resource
        // Jika route parameter adalah {system_log}, pastikan penamaannya cocok
        $systemLog->load('user');

        return view('logs.show', [
            'log' => $systemLog,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SystemLog $systemLog)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SystemLog $systemLog)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemLog $systemLog)
    {
        $systemLog->delete();
        return redirect()->route('system-logs.index')->with('success', 'Log berhasil dihapus');
    }

    /**
     * Hapus semua logs yang lebih lama dari X hari (Custom Method)
     */
    public function clearOldLogs(Request $request)
    {
        $days = $request->input('days', 30); 

        $deletedCount = SystemLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('system-logs.index')
            ->with('success', "Log yang lebih lama dari {$days} hari berhasil dihapus. Total: {$deletedCount} records");
    }

    /**
     * Export logs ke CSV (Custom Method)
     */
    public function export(Request $request)
    {
        // Logika export tidak berubah, tetap menggunakan get()
        // ... (Logika filter query sama seperti index) ...
        $query = SystemLog::with('user');
        
        // Asumsi query filter diletakkan di sini sama persis dengan fungsi index...

        $logs = $query->orderBy('created_at', 'desc')->get();

        $csv = "User ID,Username,Method,URL,IP Address,Aksi,Status Code,Exception Class,Exception Message,Waktu\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $log->user_id,
                $log->user->name ?? '-',
                $log->method,
                $log->url,
                $log->ip_address,
                $log->aksi,
                $log->status_code ?? '-',
                $log->exception_class ?? '-',
                str_replace('"', "'", $log->exception_message ?? '-'),
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="system-logs-' . now()->format('Y-m-d-H-i-s') . '.csv"',
        ]);
    }
}