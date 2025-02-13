<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller implements HasMiddleware // Menggunakan Middleware untuk Spatie Permission
{
    /**
     * Menentukan middleware untuk membatasi akses berdasarkan izin pengguna.
     */
    public static function middleware()
    {
        return [
            new Middleware('permission:roles index', only: ['index']), // Hanya pengguna dengan izin 'roles index' yang bisa mengakses index()
            new Middleware('permission:roles create', only: ['create', 'store']), // Hanya pengguna dengan izin 'roles create' yang bisa mengakses create() dan store()
            new Middleware('permission:roles edit', only: ['edit', 'update']), // Hanya pengguna dengan izin 'roles edit' yang bisa mengakses edit() dan update()
            new Middleware('permission:roles delete', only: ['destroy']), // Hanya pengguna dengan izin 'roles delete' yang bisa menghapus role
        ];
    }

    /**
     * Menampilkan daftar role yang tersedia.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Mengambil data roles beserta permissions yang terkait
        $roles = Role::select('id', 'name')
            ->with('permissions:id,name')
            ->when($request->search, fn($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(6);

        // Menampilkan halaman daftar roles menggunakan Inertia.js
        return inertia('Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Menampilkan halaman form untuk membuat role baru.
     */
    public function create()
    {
        // Mengambil semua permission dan mengelompokkannya berdasarkan kata pertama dalam nama permission
        $data = Permission::orderBy('name')->pluck('name', 'id');
        $collection = collect($data);
        $permissions = $collection->groupBy(function ($item) {
            return explode(' ', $item)[0]; // Mengambil kata pertama dari nama permission
        });

        // Menampilkan halaman pembuatan role dengan daftar permission
        return inertia('Roles/Create', ['permissions' => $permissions]);
    }

    /**
     * Menyimpan role baru ke dalam database.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input dari request
        $request->validate([
            'name' => 'required|min:3|max:255|unique:roles', // Nama role harus unik
            'selectedPermissions' => 'required|array|min:1', // Harus memilih setidaknya satu permission
        ]);

        // Membuat role baru
        $role = Role::create(['name' => $request->name]);

        // Memberikan permissions kepada role
        $role->givePermissionTo($request->selectedPermissions);

        // Redirect kembali ke halaman daftar roles dengan pesan sukses
        return to_route('roles.index')->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Menampilkan halaman form untuk mengedit role yang dipilih.
     * 
     * @param Role $role
     * @return \Inertia\Response
     */
    public function edit(Role $role)
    {
        // Mengambil semua permission dan mengelompokkannya berdasarkan kata pertama dalam nama permission
        $data = Permission::orderBy('name')->pluck('name', 'id');
        $collection = collect($data);
        $permissions = $collection->groupBy(function ($item) {
            return explode(' ', $item)[0];
        });

        // Memuat permissions yang dimiliki oleh role
        $role->load('permissions');

        // Menampilkan halaman edit role dengan data role dan permissions yang tersedia
        return inertia('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * Memperbarui data role yang dipilih.
     * 
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Role $role)
    {
        // Validasi input agar nama role tetap unik kecuali untuk dirinya sendiri
        $request->validate([
            'name' => 'required|min:3|max:255|unique:roles,name,' . $role->id,
            'selectedPermissions' => 'required|array|min:1',
        ]);

        // Memperbarui nama role
        $role->update(['name' => $request->name]);

        // Menyesuaikan permissions yang dimiliki oleh role
        $role->syncPermissions($request->selectedPermissions);

        // Redirect kembali ke halaman daftar roles dengan pesan sukses
        return to_route('roles.index')->with('success', 'Role berhasil diperbarui!');
    }

    /**
     * Menghapus role yang dipilih dari database.
     * 
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        // Menghapus role
        $role->delete();

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Role berhasil dihapus!');
    }
}
