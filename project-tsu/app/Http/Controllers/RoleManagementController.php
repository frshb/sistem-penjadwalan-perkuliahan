<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'Super Admin');
        $prodiId = $request->query('prodi_id');

        $order = ['admin', 'kaprodi', 'sekretaris prodi', 'dekan', 'dosen', 'mahasiswa'];
        $roles = \App\Models\Role::all()->sortBy(function($role) use ($order) {
            $pos = array_search($role->nama_role, $order);
            return $pos !== false ? $pos : 999;
        });
        $rolesData = [];

        foreach ($roles as $role) {
            $displayName = $role->nama_role;
            if ($role->nama_role === 'admin') {
                $displayName = 'Super Admin';
            } else {
                $displayName = ucfirst($role->nama_role);
            }

            if ($displayName === $activeTab && $prodiId && in_array($role->nama_role, ['kaprodi', 'sekretaris prodi'])) {
                $rolesData[$displayName] = $role->getMergedPermissions($prodiId);
            } else {
                $rolesData[$displayName] = $role->getMergedPermissions();
            }
        }

        $prodis = \App\Models\Prodi::where('nama_prodi', '!=', 'Dosen Eksternal Fakultas')
            ->orderBy('nama_prodi')
            ->get();

        return view('settings.roles.index', compact('rolesData', 'prodis', 'activeTab', 'prodiId'));
    }

    public function update(Request $request)
    {
        $submittedPermissions = $request->input('permissions', []);
        $activeTab = $request->input('active_tab');
        $prodiId = $request->input('prodi_id');

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
                                'access' => $item['access'] ?? 'read',
                            ];
                        }
                    }

                    $cleanedModules[$moduleName] = [
                        'enabled' => $enabled,
                        'items' => $items,
                    ];
                }

                $currentPermissions = is_array($role->permissions) ? $role->permissions : [];

                if ($prodiId && in_array($roleName, ['kaprodi', 'sekretaris prodi'])) {
                    // Update konfigurasi spesifik prodi
                    $currentPermissions["prodi_{$prodiId}"] = $cleanedModules;
                } else {
                    // Update konfigurasi default/global, pertahankan override prodi yang ada
                    foreach ($cleanedModules as $modKey => $modVal) {
                        $currentPermissions[$modKey] = $modVal;
                    }
                }

                $role->permissions = $currentPermissions;
                $role->save();
            }
        }

        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }
}
