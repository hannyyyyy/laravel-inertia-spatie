<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Menentukan middleware untuk membatasi akses berdasarkan izin pengguna.
     */
    public static function middleware()
    {
        return [
            new Middleware('permission:users index', only: ['index']), // Hanya pengguna dengan izin 'users index' yang bisa melihat daftar pengguna
            new Middleware('permission:users create', only: ['create', 'store']), // Hanya pengguna dengan izin 'users create' yang bisa membuat pengguna baru
            new Middleware('permission:users edit', only: ['edit', 'update']), // Hanya pengguna dengan izin 'users edit' yang bisa mengedit pengguna
            new Middleware('permission:users delete', only: ['destroy']), // Hanya pengguna dengan izin 'users delete' yang bisa menghapus pengguna
        ];
    }

    /**
     * Menampilkan daftar semua pengguna dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        // Mengambil semua pengguna dengan peran mereka
        $users = User::with('roles')
            ->when($request->search, fn($query) => $query->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(6);

        // Menampilkan halaman daftar pengguna dengan filter pencarian
        return inertia('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Menampilkan halaman formulir untuk membuat pengguna baru.
     */
    public function create()
    {
        // Mengambil semua peran (roles) terbaru
        $roles = Role::latest()->get();

        // Menampilkan halaman pembuatan pengguna
        return inertia('Users/Create', ['roles' => $roles]);
    }

    /**
     * Menyimpan pengguna baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi data dari request
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:4', // Harus dikonfirmasi (password_confirmation)
            'selectedRoles' => 'required|array|min:1', // Setidaknya satu peran harus dipilih
        ]);

        // Membuat pengguna baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Enkripsi password
        ]);

        // Menambahkan peran (roles) ke pengguna
        $user->assignRole($request->selectedRoles);

        // Redirect kembali ke halaman daftar pengguna dengan pesan sukses
        return to_route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Menampilkan halaman formulir untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        // Mengambil semua peran kecuali 'super-admin'
        $roles = Role::where('name', '!=', 'super-admin')->get();

        // Memuat peran pengguna
        $user->load('roles');

        // Menampilkan halaman edit pengguna dengan daftar peran
        return inertia('Users/Edit', ['user' => $user, 'roles' => $roles]);
    }

    /**
     * Memperbarui data pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        // Validasi data dari request
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'selectedRoles' => 'required|array|min:1', // Pengguna harus memiliki setidaknya satu peran
        ]);

        // Memperbarui informasi pengguna
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Menyesuaikan peran pengguna
        $user->syncRoles($request->selectedRoles);

        // Redirect kembali ke halaman daftar pengguna dengan pesan sukses
        return to_route('users.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Menghapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        // Menghapus pengguna
        $user->delete();

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Pengguna berhasil dihapus!');
    }
}
