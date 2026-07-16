<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Complaint::with(['user', 'location', 'category', 'assignedTechnician']);
        
        // Filter berdasarkan role
        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'teknisi') {
            $query->where('assigned_to', $user->id);
        }
        

        $complaints = $query->latest()->get();

        return view('complaints.index', compact('complaints'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeCreate();

        $locations = Location::orderBy('room_name')->get();
        $categories = Category::orderBy('name')->get();
        
        return view('complaints.create', compact('locations', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeCreate();

        $validated = $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'location_id' => 'required|exists:locations,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo_path')) {
            $validated['photo_path'] = $request->file('photo_path')->store('complaints', 'public');
        }
        
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'menunggu';

        $complaint = Complaint::create($validated);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'actor_id' => Auth::id(),
            'old_status' => null,
            'new_status' => 'menunggu',
            'log_message' => 'Pengaduan dibuat oleh pelapor.',
        ]);

        return redirect()->route('complaints.show', $complaint->id)
            ->with('success', 'Pengaduan berhasil dikirim.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Complaint $complaint)
    {
        $this->authorizeView($complaint);

        
        $complaint->load(['user', 'location', 'category', 'assignedTechnician', 'photos', 'logs.actor']);

        return view('complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Complaint $complaint)
    {
        $this->authorizeUpdate($complaint);

        $technicians = collect(); 

        if (Auth::user()->role === 'admin') {
            $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        }

        return view('complaints.edit', compact('complaint', 'technicians')); // Typo folder diperbaiki
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $this->authorizeUpdate($complaint);

        $user = Auth::user();

        // Validasi dasar
        $rules = [
            'new_status' => 'required|in:diproses,selesai,ditolak',
            'log_message' => 'required|string|max:1000',
        ];

        // Validasi tambahan khusus admin jika status diproses
        if ($user->role === 'admin' && $request->new_status === 'diproses') {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $oldStatus = $complaint->status;
        $newStatus = $validated['new_status'];

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'selesai') {
            $updateData['resolved_at'] = now();
        } else {
            $updateData['resolved_at'] = null;
        }
        
        // Assign teknisi hanya boleh dilakukan admin
        if ($user->role === 'admin' && $newStatus === 'diproses') {
            $updateData['assigned_to'] = $validated['assigned_to'];
        }

        $complaint->update($updateData);

        ComplaintLog::create([
            'complaint_id' => $complaint->id,
            'actor_id' => $user->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'log_message' => $validated['log_message'],
        ]);

        return redirect()->route('complaints.show', $complaint->id)
            ->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Complaint $complaint)
    {
        // Perbaikan logika OR ke in_array agar admin dan dev bisa menghapus
        if (!in_array(Auth::user()->role, ['admin', 'dev'])) {
            abort(403, 'Unauthorized');
        }

        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Pengaduan berhasil dihapus.');
    }

    // --- HELPER METHODS ---

    protected function authorizeCreate()
    {
        // Perbaikan penempatan titik koma (;) yang menghentikan proses
        if (!in_array(Auth::user()->role, ['user', 'admin', 'dev'])) {
            abort(403, 'Anda tidak memiliki izin membuat pengaduan.');
        }
    }

    protected function authorizeView(Complaint $complaint)
    {
        $user = Auth::user();

        // User hanya bisa melihat aduan buatannya sendiri
        if ($user->role === 'user' && $complaint->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat pengaduan milik sendiri.');
        }

        // Teknisi hanya bisa melihat aduan yang ditugaskan kepadanya
        if ($user->role === 'teknisi' && $complaint->assigned_to !== $user->id) {
            abort(403, 'Anda hanya dapat melihat tugas yang ditugaskan kepada Anda.');
        }
    }

    protected function authorizeUpdate(Complaint $complaint)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'dev', 'teknisi'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit pengaduan.');
        }
        
        // Teknisi hanya bisa mengubah pengaduan yang ditugaskan padanya
        if ($user->role === 'teknisi' && $complaint->assigned_to !== $user->id) {
            abort(403, 'Anda hanya dapat mengedit tugas yang ditugaskan kepada Anda.');
        }
    }
}