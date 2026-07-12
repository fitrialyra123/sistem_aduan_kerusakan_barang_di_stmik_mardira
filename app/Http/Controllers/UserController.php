<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (dengan filter opsional).
     * Otorisasi akses halaman ditangani middleware route (mis. role:admin,dev).
     */
    public function index(Request $request)
    {
        $query = User::query()->orderBy('name');

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Pencarian berdasarkan nama, email, atau nomor_identitas
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_identitas', 'like', "%{$search}%");
            });
        }

        // Dev tidak boleh melihat teknisi
        if (Auth::user()->role === 'dev') {
            $query->where('role', '!=', 'teknisi');
        }

        $users = $query->get();

        return view('users.index', compact('users'));
    }

    /**
     * Form tambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'nomor_identitas' => 'nullable|string|max:50|unique:users,nomor_identitas',
            'role' => ['required', Rule::in(['user', 'admin', 'dev', 'teknisi'])],
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Dev tidak boleh membuat akun teknisi
        if (Auth::user()->role === 'dev' && $validated['role'] === 'teknisi') {
            abort(403, 'Dev tidak dapat membuat akun teknisi.');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu pengguna.
     */
    public function show(User $user)
    {
        if (Auth::user()->role === 'dev' && $user->role === 'teknisi') {
            abort(403, 'Dev tidak dapat melihat detail akun teknisi.');
        }

        return view('users.show', compact('user'));
    }

    /**
     * Form edit pengguna.
     */
    public function edit(User $user)
    {
        // Dev tidak boleh mengedit teknisi
        if (Auth::user()->role === 'dev' && $user->role === 'teknisi') {
            abort(403, 'Dev tidak dapat mengedit akun teknisi.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update data pengguna.
     */
    public function update(Request $request, User $user)
    {
        // Dev tidak boleh mengedit teknisi
        if (Auth::user()->role === 'dev' && $user->role === 'teknisi') {
            abort(403, 'Dev tidak dapat mengedit akun teknisi.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30|unique:users,phone,' . $user->id,
            'nomor_identitas' => 'nullable|string|max:50|unique:users,nomor_identitas,' . $user->id,
            'role' => ['required', Rule::in(['user', 'admin', 'dev', 'teknisi'])],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Jika nomor_identitas diubah, reset status verifikasi
        // supaya harus diverifikasi ulang oleh admin/dev.
        if ($validated['nomor_identitas'] !== $user->nomor_identitas) {
            $validated['is_verified'] = false;
            $validated['verified_by'] = null;
            $validated['verified_at'] = null;
        }

        $data = $request->except(['password', 'password_confirmation']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        // Password hanya diupdate jika diisi
        // if (empty($validated['password'])) {
        //     unset($validated['password']);
        // } else {
        //     $validated['password'] = Hash::make($validated['password']);
        // }

        // $user->update($validated);
        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        // Tidak bisa menghapus diri sendiri
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Dev tidak boleh menghapus teknisi
        if (Auth::user()->role === 'dev' && $user->role === 'teknisi') {
            abort(403, 'Dev tidak dapat menghapus akun teknisi.');
        }

        // Cegah penghapusan jika teknisi sedang ditugaskan (opsional, untuk integritas)
        if ($user->role === 'teknisi' && $user->assignedComplaints()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Teknisi ini masih memiliki pengaduan aktif. Selesaikan atau alihkan terlebih dahulu.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Verifikasi nomor_identitas pengguna oleh admin/dev.
     * Pastikan route ini dilindungi middleware role:admin,dev.
     */
    public function verify(User $user)
    {
        if (Auth::user()->role === 'dev' && $user->role === 'teknisi') {
            abort(403, 'Dev tidak dapat memverifikasi akun teknisi.');
        }

        if (empty($user->nomor_identitas)) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna belum mengisi nomor_identitas.');
        }

        $user->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Nomor identitas pengguna berhasil diverifikasi.');
    }

    /**
     * Batalkan status verifikasi (opsional, untuk koreksi kesalahan).
     */
    public function unverify(User $user)
    {
        $user->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Verifikasi nomor identitas pengguna dibatalkan.');
    }
}