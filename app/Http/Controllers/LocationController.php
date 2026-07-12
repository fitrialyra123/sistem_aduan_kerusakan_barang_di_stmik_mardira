<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();
        // Menggunakan get() sudah tepat jika nanti dipadukan dengan DataTables
        $locations = Location::orderBy('room_name')->get();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'room_name' => 'required|string|max:25|unique:locations,room_name',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail lokasi beserta riwayat pengaduan di lokasi tersebut.
     */
    public function show(Location $location)
    {
        $this->authorizeAccess();

        // Memuat relasi complaints (pengaduan) yang terkait dengan ruangan ini.
        // Jika Anda ingin menampilkan siapa pelapornya dan kategorinya, 
        // Anda bisa menggunakan nested eager loading: $location->load(['complaints.user', 'complaints.category']);
        $location->load('complaints');

        return view('locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        $this->authorizeAccess();
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'room_name' => 'required|string|max:25|unique:locations,room_name,' . $location->id,
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        $this->authorizeAccess();

        // Perlindungan yang sangat baik agar relasi data tidak rusak
        if ($location->complaints()->exists()) {
            return redirect()->route('locations.index')
                ->with('error', 'Lokasi tidak bisa dihapus karena masih digunakan oleh pengaduan.');
        }

        $location->delete();
        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    protected function authorizeAccess()
    {
        if (!in_array(Auth::user()->role, ['admin', 'dev'])) {
            abort(403, 'Anda tidak memiliki izin mengakses data lokasi.');
        }
    }
}