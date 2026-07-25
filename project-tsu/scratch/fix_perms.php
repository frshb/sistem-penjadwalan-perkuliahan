<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = \App\Models\Role::where('nama_role', 'Sekretaris Prodi')->first();
if ($role) {
    $perms = \App\Models\Permission::whereIn('nama_permission', ['Generate Jadwal', 'Penyesuaian Jadwal'])->get();
    foreach($perms as $p) {
        \App\Models\RolePermission::firstOrCreate([
            'id_role' => $role->id_role,
            'id_permission' => $p->id_permission
        ]);
    }
    echo 'Permissions added to Sekretaris Prodi';
} else {
    echo 'Role not found';
}
