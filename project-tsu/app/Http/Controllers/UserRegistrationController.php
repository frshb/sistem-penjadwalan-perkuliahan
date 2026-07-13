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
                        User::ROLE_SEKPRODI,
                        User::ROLE_DEKAN,
                        User::ROLE_DOSEN,
                    ])
                    ->orderBy('nama_role')
                    ->get();

        $prodis = Prodi::orderBy('nama_prodi')->get();
        $dosens = Dosen::with('prodi')->orderBy('nama_dosen')->get();

        $roleIds = [
            'kaprodi' => Role::where('nama_role', User::ROLE_KAPRODI)->value('id_role'),
            'sekprodi' => Role::where('nama_role', User::ROLE_SEKPRODI)->value('id_role'),
            'dekan' => Role::where('nama_role', User::ROLE_DEKAN)->value('id_role'),
            'dosen' => Role::where('nama_role', User::ROLE_DOSEN)->value('id_role'),
        ];

        $users = User::with(['role', 'prodi', 'dosen'])->orderBy('id_user', 'desc')->get();

        return view('settings.users.register', compact('roles', 'prodis', 'dosens', 'roleIds', 'users'));
    }

    public function store(Request $request)
    {
        $kaprodRole  = Role::where('nama_role', User::ROLE_KAPRODI)->value('id_role');
        $sekprodRole = Role::where('nama_role', User::ROLE_SEKPRODI)->value('id_role');
        $dosenRole   = Role::where('nama_role', User::ROLE_DOSEN)->value('id_role');
        $dekanRole   = Role::where('nama_role', User::ROLE_DEKAN)->value('id_role');

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
                    Rule::requiredIf($request->id_role == $kaprodRole || $request->id_role == $sekprodRole),
                    'exists:program_studi,id_prodi',
                ],
                'id_dosen' => [
                    'nullable',
                    Rule::requiredIf($request->id_role == $dosenRole || $request->id_role == $dekanRole || $request->id_role == $sekprodRole),
                    'exists:dosen,id_dosen',
                ],
            ],
            // ✅ Custom messages — parameter kedua validate()
            [
                'username.unique'   => 'Username "' . $request->username . '" sudah digunakan. Silakan pilih username lain.',
                'username.required' => 'Username wajib diisi.',
                'username.max'      => 'Username maksimal 50 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min'      => 'Password minimal 6 karakter.',
                'id_role.required'  => 'Role wajib dipilih.',
                'id_prodi.required' => 'Program Studi wajib dipilih untuk role Kaprodi / Sekretaris Prodi.',
                'id_dosen.required' => 'Dosen wajib dipilih untuk role Dosen / Dekan / Sekretaris Prodi.',
            ]
        );

        $userData = [
            'username'      => $request->username,
            'password_hash' => Hash::make($request->password),
            'id_role'       => $request->id_role,
        ];

        if ($request->id_role == $kaprodRole || $request->id_role == $sekprodRole) {
            $userData['id_prodi'] = $request->id_prodi;
        }

        if ($request->filled('id_dosen')) {
            $userData['id_dosen'] = $request->id_dosen;
        }

        User::create($userData);

        return redirect()->route('settings.users.register')
                        ->with('success', 'User "' . $request->username . '" berhasil dibuat.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::whereIn('nama_role', [
                        User::ROLE_KAPRODI,
                        User::ROLE_SEKPRODI,
                        User::ROLE_DEKAN,
                        User::ROLE_DOSEN,
                    ])
                    ->orderBy('nama_role')
                    ->get();
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $dosens = Dosen::with('prodi')->orderBy('nama_dosen')->get();

        $roleIds = [
            'kaprodi' => Role::where('nama_role', User::ROLE_KAPRODI)->value('id_role'),
            'sekprodi' => Role::where('nama_role', User::ROLE_SEKPRODI)->value('id_role'),
            'dekan' => Role::where('nama_role', User::ROLE_DEKAN)->value('id_role'),
            'dosen' => Role::where('nama_role', User::ROLE_DOSEN)->value('id_role'),
        ];

        return view('settings.users.edit', compact('user', 'roles', 'prodis', 'dosens', 'roleIds'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $kaprodRole  = Role::where('nama_role', User::ROLE_KAPRODI)->value('id_role');
        $sekprodRole = Role::where('nama_role', User::ROLE_SEKPRODI)->value('id_role');
        $dosenRole   = Role::where('nama_role', User::ROLE_DOSEN)->value('id_role');
        $dekanRole   = Role::where('nama_role', User::ROLE_DEKAN)->value('id_role');

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user', 'username')->ignore($user->id_user, 'id_user'),
            ],
            'password' => 'nullable|string|min:6',
            'id_role'  => 'required|exists:role,id_role',
            'id_prodi' => [
                'nullable',
                Rule::requiredIf($request->id_role == $kaprodRole || $request->id_role == $sekprodRole),
                'exists:program_studi,id_prodi',
            ],
            'id_dosen' => [
                'nullable',
                Rule::requiredIf($request->id_role == $dosenRole || $request->id_role == $dekanRole || $request->id_role == $sekprodRole),
                'exists:dosen,id_dosen',
            ],
        ], [
            'username.unique'   => 'Username "' . $request->username . '" sudah digunakan.',
            'username.required' => 'Username wajib diisi.',
            'id_role.required'  => 'Role wajib dipilih.',
            'id_prodi.required' => 'Program Studi wajib dipilih untuk role Kaprodi / Sekretaris Prodi.',
            'id_dosen.required' => 'Dosen wajib dipilih untuk role Dosen / Dekan / Sekretaris Prodi.',
        ]);

        $userData = [
            'username' => $request->username,
            'id_role'  => $request->id_role,
            'id_prodi' => ($request->id_role == $kaprodRole || $request->id_role == $sekprodRole) ? $request->id_prodi : null,
            'id_dosen' => $request->filled('id_dosen') ? $request->id_dosen : null,
        ];

        if ($request->filled('password')) {
            $userData['password_hash'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('settings.users.register')
                         ->with('success', 'User "' . $user->username . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        return redirect()->route('settings.users.register')
                         ->with('success', 'User "' . $username . '" berhasil dihapus.');
    }
}
