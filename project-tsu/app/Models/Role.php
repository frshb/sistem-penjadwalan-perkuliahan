<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';
    protected $primaryKey = 'id_role';
    public $timestamps = false;

    protected $fillable = [
        'nama_role',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }

    public function getDefaultPermissions()
    {
        $roleNameLower = strtolower($this->nama_role);

        $managementDataItems = [
            ['name' => 'Management Prodi', 'enabled' => false],
            ['name' => 'Management Ruangan', 'enabled' => false],
            ['name' => 'Management Mata Kuliah', 'enabled' => false],
            ['name' => 'Management Data Dosen', 'enabled' => false],
            ['name' => 'Management Kelas', 'enabled' => false],
            ['name' => 'Management Data Mahasiswa', 'enabled' => false],
            ['name' => 'Management KP & Skripsi', 'enabled' => false],
        ];

        $modulPenjadwalanItems = [
            ['name' => 'Penjadwalan Otomatis', 'enabled' => false],
            ['name' => 'Penjadwalan Manual', 'enabled' => false],
        ];

        if ($roleNameLower === 'admin' || $roleNameLower === 'super admin' || $roleNameLower === 'super_admin') {
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
            // Enable Prodi, Ruangan, Mata Kuliah, Dosen, Kelas, Mahasiswa
            foreach ($managementDataItems as &$item) {
                if (in_array($item['name'], [
                    'Management Prodi',
                    'Management Ruangan',
                    'Management Mata Kuliah',
                    'Management Data Dosen',
                    'Management Kelas',
                    'Management Data Mahasiswa'
                ])) {
                    $item['enabled'] = true;
                }
            }
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
            // Enable Prodi, Mata Kuliah, Dosen
            foreach ($managementDataItems as &$item) {
                if (in_array($item['name'], [
                    'Management Prodi',
                    'Management Mata Kuliah',
                    'Management Data Dosen'
                ])) {
                    $item['enabled'] = true;
                }
            }
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

        if ($roleNameLower === 'dosen') {
            // Enable KP & Skripsi
            foreach ($managementDataItems as &$item) {
                if ($item['name'] === 'Management KP & Skripsi') {
                    $item['enabled'] = true;
                }
            }
            return [
                'management_data' => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
            ];
        }

        if ($roleNameLower === 'mahasiswa') {
            // Enable KP & Skripsi
            foreach ($managementDataItems as &$item) {
                if ($item['name'] === 'Management KP & Skripsi') {
                    $item['enabled'] = true;
                }
            }
            return [
                'management_data' => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
            ];
        }

        return [
            'management_data' => ['enabled' => false, 'items' => $managementDataItems],
            'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
        ];
    }

    public function getMergedPermissions()
    {
        $permissions = $this->permissions;
        $defaults = $this->getDefaultPermissions();

        if (empty($permissions) || !is_array($permissions)) {
            return $defaults;
        }

        // Merge management_data items
        if (isset($permissions['management_data'])) {
            $defaults['management_data']['enabled'] = (bool)($permissions['management_data']['enabled'] ?? $defaults['management_data']['enabled']);
            
            // Map items by name
            $storedItems = [];
            if (isset($permissions['management_data']['items']) && is_array($permissions['management_data']['items'])) {
                foreach ($permissions['management_data']['items'] as $item) {
                    if (isset($item['name'])) {
                        $storedItems[$item['name']] = (bool)($item['enabled'] ?? false);
                    }
                }
            }

            foreach ($defaults['management_data']['items'] as &$defaultItem) {
                if (isset($storedItems[$defaultItem['name']])) {
                    $defaultItem['enabled'] = $storedItems[$defaultItem['name']];
                }
            }
        }

        // Merge modul_penjadwalan items
        if (isset($permissions['modul_penjadwalan'])) {
            $defaults['modul_penjadwalan']['enabled'] = (bool)($permissions['modul_penjadwalan']['enabled'] ?? $defaults['modul_penjadwalan']['enabled']);
            
            // Map items by name
            $storedItems = [];
            if (isset($permissions['modul_penjadwalan']['items']) && is_array($permissions['modul_penjadwalan']['items'])) {
                foreach ($permissions['modul_penjadwalan']['items'] as $item) {
                    if (isset($item['name'])) {
                        $storedItems[$item['name']] = (bool)($item['enabled'] ?? false);
                    }
                }
            }

            foreach ($defaults['modul_penjadwalan']['items'] as &$defaultItem) {
                if (isset($storedItems[$defaultItem['name']])) {
                    $defaultItem['enabled'] = $storedItems[$defaultItem['name']];
                }
            }
        }

        return $defaults;
    }

    public function hasPermission($module, $item = null)
    {
        $permissions = $this->getMergedPermissions();

        if (!isset($permissions[$module])) {
            return false;
        }

        $moduleData = $permissions[$module];
        if (!$moduleData['enabled']) {
            return false;
        }

        if ($item === null) {
            return true;
        }

        if (isset($moduleData['items']) && is_array($moduleData['items'])) {
            foreach ($moduleData['items'] as $pItem) {
                if ($pItem['name'] === $item) {
                    return (bool)$pItem['enabled'];
                }
            }
        }

        return false;
    }
}
