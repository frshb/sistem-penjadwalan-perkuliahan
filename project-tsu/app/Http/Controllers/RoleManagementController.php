<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
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

            $rolesData[$displayName] = $role->getMergedPermissions();
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
