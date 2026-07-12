<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori.
     */
    public function index()
    {
        $this->authorizeAccess();

        $categories = Category::orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori.
     */
    public function create()
    {
        $this->authorizeAccess();

        return view('categories.create');
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            // Nama tabel database adalah categories
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit kategori.
     */
    public function edit(Category $category)
    {
        $this->authorizeAccess();

        return view('categories.edit', compact('category'));
    }

    public function show(Category $category) {
        abort(404);
    }
    /**
     * Update kategori.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori.
     */
    public function destroy(Category $category)
    {
        $this->authorizeAccess();

        // Cek apakah kategori masih digunakan di pengaduan
        if ($category->complaints()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh pengaduan.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Otorisasi: hanya Admin dan Dev yang bisa mengakses.
     */
    protected function authorizeAccess()
    {
        if (!in_array(Auth::user()->role, ['admin', 'dev'])) {
            abort(403, 'Anda tidak memiliki izin mengakses data kategori.');
        }
    }
}