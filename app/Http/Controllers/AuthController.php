<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Dosen; // Import Dosen
use App\Models\Prodi; // Import Prodi

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Custom auth attempt since we use password_hash and not standard 'password' field logic potentially?
        // Laravel's Auth facade expects 'password' key in criteria if using standard EloquentUserProvider.
        // User model `getAuthPassword` returns `password_hash`.
        // If we pass ['username' => ..., 'password' => ...], Laravel automatically hashes 'password' and compares with model's auth password.
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard')->with('login_success', true);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function register()
    {
        return view('auth.register', [
            'roles' => Role::all(),
            'dosens' => Dosen::with('prodi')->get(), // Pass dosens with prodi for linking
            'prodis' => Prodi::all(), // Pass prodis for Kaprodi linking
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:user',
            'password' => 'required|min:6',
            'id_role' => 'required|exists:role,id_role',
            'id_dosen' => 'nullable|exists:dosen,id_dosen', // Validate id_dosen
            'id_prodi' => 'nullable|exists:program_studi,id_prodi', // Validate id_prodi
        ]);

        // Admin limit check
        if ($validated['id_role'] == User::ROLE_ADMIN) {
            $adminCount = User::where('id_role', User::ROLE_ADMIN)->count();
            if ($adminCount >= 3) {
                return back()->withErrors(['id_role' => 'Maksimal 3 Admin diperbolehkan.']);
            }
        }

        // Check if role requires mapping
        $roleId = (int)$validated['id_role'];
        
        // Kaprodi must start with Prodi
        if ($roleId === User::ROLE_KAPRODI && empty($validated['id_prodi'])) {
             return back()->withInput()->withErrors(['id_prodi' => 'Program Studi harus dipilih untuk Kaprodi.']);
        }

        // Dosen must start with Dosen
        if ($roleId === User::ROLE_DOSEN && empty($validated['id_dosen'])) {
             return back()->withInput()->withErrors(['id_dosen' => 'Dosen harus dipilih untuk role Dosen.']);
        }
        
        // Create User
        $nextId = User::max('id_user') + 1;

        $user = User::create([
            'id_user' => $nextId,
            'username' => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'id_role' => $validated['id_role'],
            'id_dosen' => $validated['id_dosen'] ?? null,
            'id_prodi' => $validated['id_prodi'] ?? null,
        ]);

        return redirect()->route('dashboard')->with('success', 'User berhasil dibuat.');
    }
    public function forgotPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:user,username',
            'old_password' => 'required', // Requested as "tidak perlu sama persis" (loose check)
            'new_password' => 'required|min:6',
        ]);

        $user = User::where('username', $request->username)->first();
        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }
}
