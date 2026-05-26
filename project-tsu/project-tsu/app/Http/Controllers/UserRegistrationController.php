<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Prodi;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserRegistrationController extends Controller
{
    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Only fetch roles relevant for this feature: Kaprodi (2), Dekan (3), Dosen (4)
        // Adjust IDs based on User model constants if needed, but fetching from DB is safer if dynamic.
        // Based on User model constants: ADMIN=1, KAPRODI=2, DEKAN=3, DOSEN=4, MHS=5
        $roles = Role::whereIn('id_role', [User::ROLE_KAPRODI, User::ROLE_DEKAN, User::ROLE_DOSEN])->get();
        
        $prodis = Prodi::all();
        $dosens = Dosen::all(); // Might be a long list, consider optimized loading or search later

        return view('settings.users.create', compact('roles', 'prodis', 'dosens'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:user,username',
            'password' => 'required|string|min:6',
            'id_role' => 'required|exists:role,id_role',
            // Conditional validation based on role
            'id_prodi' => [
                'nullable', 
                Rule::requiredIf(function () use ($request) {
                    return $request->id_role == User::ROLE_KAPRODI;
                }),
                'exists:program_studi,id_prodi'
            ],
            'id_dosen' => 'nullable|exists:dosen,id_dosen',
        ]);

        // Find max ID manually since not auto-increment (based on User model observation)
        $maxId = User::max('id_user') ?? 0;
        $newId = $maxId + 1;

        $userData = [
            'id_user' => $newId,
            'username' => $request->username,
            'password_hash' => Hash::make($request->password), // Using password_hash column
            'id_role' => $request->id_role,
        ];

        // Link to Prodi if Kaprodi
        if ($request->id_role == User::ROLE_KAPRODI) {
            $userData['id_prodi'] = $request->id_prodi;
        }

        // Link to Dosen if Dosen or Kaprodi (optional logic, but commonly Kaprodi is also a Dosen)
        if ($request->filled('id_dosen')) {
            $userData['id_dosen'] = $request->id_dosen;
        }

        User::create($userData);

        return redirect()->route('settings.users.create')->with('success', 'User berhasil dibuat.');
    }
}
