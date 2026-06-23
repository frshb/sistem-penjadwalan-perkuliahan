<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Prodi;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Helpers\ProdiFilter;

class UserRegistrationController extends Controller
{
    public function create()
    {
        // ✅ Query berdasarkan nama_role, bukan id_role integer
        $roles = Role::whereIn('nama_role', [
                        User::ROLE_KAPRODI,
                        User::ROLE_DEKAN,
                        User::ROLE_DOSEN,
                    ])
                    ->orderBy('nama_role')
                    ->get();

        $prodis = Prodi::orderBy('nama_prodi')->get();
        $dosens = Dosen::with('prodi')->orderBy('nama_dosen')->get();

        return view('settings.users.create', compact('roles', 'prodis', 'dosens'));
    }

    public function store(Request $request)
    {
        $kaprodRole = Role::where('nama_role', User::ROLE_KAPRODI)->value('id_role');
        $dosenRole  = Role::where('nama_role', User::ROLE_DOSEN)->value('id_role');

        $request->validate(
            // ✅ Rules
            [
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('user', 'username'),
                ],
                'password' => 'required|string|min:6',
                'id_role'  => 'required|exists:role,id_role',
                'id_prodi' => [
                    'nullable',
                    Rule::requiredIf($request->id_role == $kaprodRole),
                    'exists:program_studi,id_prodi',
                ],
                'id_dosen' => 'nullable|exists:dosen,id_dosen',
            ],
            // ✅ Custom messages — parameter kedua validate()
            [
                'username.unique'   => 'Username "' . $request->username . '" sudah digunakan. Silakan pilih username lain.',
                'username.required' => 'Username wajib diisi.',
                'username.max'      => 'Username maksimal 50 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min'      => 'Password minimal 6 karakter.',
                'id_role.required'  => 'Role wajib dipilih.',
                'id_prodi.required' => 'Program Studi wajib dipilih untuk role Kaprodi.',
            ]
        );

        $userData = [
            'username'      => $request->username,
            'password_hash' => Hash::make($request->password),
            'id_role'       => $request->id_role,
        ];

        if ($request->id_role == $kaprodRole) {
            $userData['id_prodi'] = $request->id_prodi;
        }

        if ($request->filled('id_dosen')) {
            $userData['id_dosen'] = $request->id_dosen;
        }

        User::create($userData);

        return redirect()->route('settings.users.create')
                        ->with('success', 'User "' . $request->username . '" berhasil dibuat.');
    }
}
