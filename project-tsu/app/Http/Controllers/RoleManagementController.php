<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    private function getDefaultPermissions($roleName)
    {
        $roleNameLower = strtolower($roleName);

        $managementDataItems = [
            ['name' => 'Management Prodi', 'enabled' => false],
            ['name' => 'Management Ruangan', 'enabled' => false],
            ['name' => 'Management Mata Kuliah', 'enabled' => false],
            ['name' => 'Management Data Dosen', 'enabled' => false],
            ['name' => 'Management Data Mahasiswa', 'enabled' => false],
            ['name' => 'Management KP & Skripsi', 'enabled' => false],
        ];

        $modulPenjadwalanItems = [
            ['name' => 'Penjadwalan Otomatis', 'enabled' => false],
            ['name' => 'Penjadwalan Manual', 'enabled' => false],
        ];

        if ($roleNameLower === 'admin' || $roleNameLower === 'super admin') {
            foreach ($managementDataItems as &$item) {
                $item['enabled'] = true;
            }
            foreach ($modulPenjadwalanItems as &$item) {
                $item['enabled'] = true;
            }
            return [
                'management_data' => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => true, 'items' => $modulPenjadwalanItems],
            ];
        }

        if ($roleNameLower === 'kaprodi') {
            $managementDataItems[1]['enabled'] = true; // Ruangan
            $managementDataItems[2]['enabled'] = true; // Matkul
            $managementDataItems[3]['enabled'] = true; // Dosen
            $managementDataItems[4]['enabled'] = true; // Mahasiswa
            return [
                'management_data' => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => [
                    'enabled' => true,
                    'items' => [
                        ['name' => 'Penjadwalan Otomatis', 'enabled' => true],
                        ['name' => 'Penjadwalan Manual', 'enabled' => true],
                    ]
                ],
            ];
        }

        if ($roleNameLower === 'dekan') {
            return [
                'management_data' => ['enabled' => false, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
            ];
        }

        if ($roleNameLower === 'dosen') {
            $managementDataItems[5]['enabled'] = true; // KP & Skripsi
            return [
                'management_data' => ['enabled' => false, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
            ];
        }

        if ($roleNameLower === 'mahasiswa') {
            $managementDataItems[5]['enabled'] = true; // KP & Skripsi
            return [
                'management_data' => ['enabled' => false, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
            ];
        }

        return [
            'management_data' => ['enabled' => false, 'items' => $managementDataItems],
            'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
        ];
    }

    public function index()
    {
        $roles = \App\Models\Role::all();
        $rolesData = [];

        foreach ($roles as $role) {
            $displayName = $role->nama_role;
            if ($role->nama_role === 'admin') {
                $displayName = 'Super Admin';
            } else {
                $displayName = ucfirst($role->nama_role);
            }

            // Load permissions or default
            $permissions = $role->permissions;
            if (empty($permissions) || !is_array($permissions) || !isset($permissions['management_data']) || !isset($permissions['modul_penjadwalan'])) {
                $permissions = $this->getDefaultPermissions($role->nama_role);
            }

            $rolesData[$displayName] = $permissions;
        }

        return view('settings.roles.index', compact('rolesData'));
    }

    public function update(Request $request)
    {
        $submittedPermissions = $request->input('permissions', []);

        foreach ($submittedPermissions as $displayName => $modules) {
            $roleName = $displayName === 'Super Admin' ? 'admin' : strtolower($displayName);
            $role = \App\Models\Role::where('nama_role', $roleName)->first();

            if ($role) {
                $cleanedModules = [];
                foreach ($modules as $moduleName => $moduleData) {
                    $enabled = isset($moduleData['enabled']) && ($moduleData['enabled'] === '1' || $moduleData['enabled'] === true);
                    
                    $items = [];
                    if (isset($moduleData['items']) && is_array($moduleData['items'])) {
                        foreach ($moduleData['items'] as $item) {
                            $items[] = [
                                'name' => $item['name'] ?? '',
                                'enabled' => isset($item['enabled']) && ($item['enabled'] === '1' || $item['enabled'] === true),
                            ];
                        }
                    }

                    $cleanedModules[$moduleName] = [
                        'enabled' => $enabled,
                        'items' => $items,
                    ];
                }

                $role->permissions = $cleanedModules;
                $role->save();
            }
        }

        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }
}
