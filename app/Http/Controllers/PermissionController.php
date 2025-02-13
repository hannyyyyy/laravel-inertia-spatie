<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * Menentukan middleware untuk setiap metode dalam controller ini.
     * Middleware memastikan hanya pengguna dengan izin tertentu yang dapat mengakses metode terkait.
     */
    public static function middleware()
    {
        return [
            new Middleware('permission:permissions index', only: ['index']), // Hanya pengguna dengan izin 'permissions index' yang bisa mengakses index()
            new Middleware('permission:permissions create', only: ['create', 'store']), // Hanya pengguna dengan izin 'permissions create' yang bisa mengakses create() dan store()
            new Middleware('permission:permissions edit', only: ['edit', 'update']), // Hanya pengguna dengan izin 'permissions edit' yang bisa mengakses edit() dan update()
            new Middleware('permission:permissions delete', only: ['destroy']), // Hanya pengguna dengan izin 'permissions delete' yang bisa mengakses destroy()
        ];
    }

    /**
     * Menampilkan daftar izin (permissions) yang tersedia.
     * Menerapkan pencarian berdasarkan nama jika terdapat query 'search'.
     */
    public function index(Request $request)
    {
        // Mengambil data permissions dengan filter pencarian jika ada
        $permissions = Permission::select('id', 'name')
            ->when($request->search, fn($search) => $search->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(6)
            ->withQueryString();

        // Menampilkan halaman daftar permissions menggunakan Inertia.js
        return inertia('Permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Menampilkan halaman form untuk membuat permission baru.
     */
    public function create()
    {
        return inertia('Permissions/Create');
    }

    /**
     * Menyimpan data permission baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi input dari request
        $request->validate([
            'name' => 'required|min:3|max:255|unique:permissions'
        ]);

        // Membuat permission baru
        Permission::create(['name' => $request->name]);

        // Redirect ke halaman index dengan flash message
        return to_route('permissions.index')->with('success', 'Permission berhasil ditambahkan!');
    }

    /**
     * Menampilkan halaman form untuk mengedit permission yang dipilih.
     */
    public function edit(Permission $permission)
    {
        return inertia('Permissions/Edit', ['permission' => $permission]);
    }

    /**
     * Memperbarui data permission yang dipilih.
     */
    public function update(Request $request, Permission $permission)
    {
        // Validasi input agar nama permission tetap unik kecuali untuk dirinya sendiri
        $request->validate([
            'name' => 'required|min:3|max:255|unique:permissions,name,' . $permission->id
        ]);

        // Memperbarui permission di database
        $permission->update(['name' => $request->name]);

        // Redirect kembali ke daftar permissions dengan pesan sukses
        return to_route('permissions.index')->with('success', 'Permission berhasil diperbarui!');
    }

    /**
     * Menghapus permission yang dipilih dari database.
     */
    public function destroy(Permission $permission)
    {
        // Menghapus permission
        $permission->delete();

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Permission berhasil dihapus!');
    }
}
