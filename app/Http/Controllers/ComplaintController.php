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
        //filer berdasarkan role
        if($user->role === 'user') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'teknisi') {
            $query->where('assigned_to', $user->id);
        }

        ///admin dan dev bisa melihat semua

        $complaints =  $query->latest()->get();

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
            'category_id' => 'required|string|exists":categories,id',
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
            'log_message' => 'Pengaduan dibut oleh pelapor',
        ]);

        return redirect()->route('complaints.show', $complaint->id)->with('success', 'Pengaduan berhassil dikirim');
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

        $technicians = collect(); // ambil teknisi

        if(Auth::user()->role === 'admin') {
            $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        }


        return view('complaint.edit', compact('complaint', 'technicians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $this->authorizeUpdate($complaint);

        $user = Auth::user();


        $rules = [
            'new_status' => 'required|in:diproses, selesai, ditolak',
            'log_message' => 'required|string|max:1000',
        ];

        if($user->role === 'admin' && $request->new_status === 'diproses') {
            $rules['assigned_to'] = 'required|exists:users,id';

            $validated = $request->validate($rules);

            $oldstatus = $complaint->status;
            $newStatus = $validated['new_status'];

            $updateData = ['status' => $newStatus];

            if ($newStatus === 'selesai') {
                $updateData['resolved_at'] = now();
            } else {
                $updateData['resolved_at'] = null;
            }
            // assign teknisi hanya boleh dengan admin
            if ($user->role === 'admin' && $newStatus === 'diproses') {
                $updateData['assigned_to'] = $validated['assigned_to'];
            }

            $complaint->update($updateData);

            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'actor_id' => $user->id,
                'old_status' => $oldstatus,
                'new_status' => $newStatus,
                'log_message' => $validated['log_message'],
            ]);

            return redirect()->route('complaints.show', $complaint->id)->with('succsess', 'Status pengaduan berhasil diperbarui');
        }
    }

    //helper methods

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Complaint $complaint)
    {
        if (Auth::user()->role !== 'admin' || Auth::user()->role !== 'dev') {
            abort(403, 'Anauthorized');
        }

        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Pengaduan berhasil dihapus');
    }

    protected function authorizeCreate() {
        if(!in_array(Auth::user()->role, ['user', 'admin', 'dev']));
        abort(403, 'anda tidak memiliki izin membuat pengaduan');
    }

    protected function authorizeView(Complaint $complaint) {
        $user = Auth::user();

        if ($user->role === 'user' && $complaint->assigned_to !== $user->id) {
            abort(403);
        }
    }
    protected function authorizeUpdate(Complaint $complaint) {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'dev', 'teknisi'])) {
            abort(403);
        }
        //teknisi hanya bisa mengubah pengaduan yang ditugaskan padanya;
        if ($user->role === 'teknisi' && $complaint->assigned_to !== $user->id) {
            abort(403);
        }
    }
}
