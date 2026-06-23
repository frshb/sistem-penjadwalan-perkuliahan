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

    public function getDefaultPermissions(): array
    {
        $roleNameLower = strtolower($this->nama_role);

        $managementDataItems = [
            ['name' => 'Program Studi',        'enabled' => false],
            ['name' => 'Ruangan',              'enabled' => false],
            ['name' => 'Mata Kuliah',          'enabled' => false],
            ['name' => 'Dosen',                'enabled' => false],
            ['name' => 'Pengampu Mata Kuliah', 'enabled' => false],
            ['name' => 'Kelas Paralel',        'enabled' => false],
            ['name' => 'Mahasiswa',            'enabled' => false],
            ['name' => 'KP & Skripsi',         'enabled' => false],
        ];

        $modulPenjadwalanItems = [
            ['name' => 'Generate Jadwal',     'enabled' => false],
            ['name' => 'Penyesuaian Jadwal',  'enabled' => false],
        ];

        $enableItems = function (array &$items, array $names): void {
            foreach ($items as &$item) {
                if (in_array($item['name'], $names, true)) {
                    $item['enabled'] = true;
                }
            }
            unset($item);
        };

        $enableAll = function (array &$items): void {
            foreach ($items as &$item) {
                $item['enabled'] = true;
            }
            unset($item);
        };

        // Super Admin
        if (in_array($roleNameLower, ['admin', 'super admin', 'super_admin'], true)) {
            $enableAll($managementDataItems);
            $enableAll($modulPenjadwalanItems);
            return [
                'management_data'   => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => true, 'items' => $modulPenjadwalanItems],
            ];
        }

        // Kaprodi
        if ($roleNameLower === 'kaprodi') {
            $enableItems($managementDataItems, [
                'Program Studi', 'Ruangan', 'Mata Kuliah',
                'Dosen', 'Pengampu Mata Kuliah', 'Kelas Paralel', 'Mahasiswa',
            ]);
            $enableAll($modulPenjadwalanItems);
            return [
                'management_data'   => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => true, 'items' => $modulPenjadwalanItems],
            ];
        }

        // Dekan
        if ($roleNameLower === 'dekan') {
            $enableItems($managementDataItems, [
                'Program Studi', 'Mata Kuliah', 'Dosen',
            ]);
            $enableAll($modulPenjadwalanItems);
            return [
                'management_data'   => ['enabled' => true, 'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => true, 'items' => $modulPenjadwalanItems],
            ];
        }

        // Dosen
        if ($roleNameLower === 'dosen') {
            $enableItems($managementDataItems, ['KP & Skripsi']);
            return [
                'management_data'   => ['enabled' => true,  'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false,  'items' => $modulPenjadwalanItems],
            ];
        }

        // Mahasiswa
        if ($roleNameLower === 'mahasiswa') {
            $enableItems($managementDataItems, ['KP & Skripsi']);
            return [
                'management_data'   => ['enabled' => true,  'items' => $managementDataItems],
                'modul_penjadwalan' => ['enabled' => false,  'items' => $modulPenjadwalanItems],
            ];
        }

        return [
            'management_data'   => ['enabled' => false, 'items' => $managementDataItems],
            'modul_penjadwalan' => ['enabled' => false, 'items' => $modulPenjadwalanItems],
        ];
    }

    public function getMergedPermissions(): array
    {
        $saved    = $this->permissions;
        $defaults = $this->getDefaultPermissions();

        // Jika belum ada permissions tersimpan, kembalikan default
        if (empty($saved) || !is_array($saved)) {
            return $defaults;
        }

        foreach (['management_data', 'modul_penjadwalan'] as $module) {
            if (!isset($saved[$module])) {
                continue;
            }

            // Override enabled flag dari data tersimpan
            $defaults[$module]['enabled'] = (bool) ($saved[$module]['enabled'] ?? $defaults[$module]['enabled']);

            // Buat map nama => enabled dari data tersimpan
            $savedItemMap = [];
            foreach ($saved[$module]['items'] ?? [] as $item) {
                if (isset($item['name'])) {
                    $savedItemMap[$item['name']] = (bool) ($item['enabled'] ?? false);
                }
            }

            // Terapkan ke default items (item baru yang belum ada di DB tetap muncul)
            foreach ($defaults[$module]['items'] as &$defaultItem) {
                if (array_key_exists($defaultItem['name'], $savedItemMap)) {
                    $defaultItem['enabled'] = $savedItemMap[$defaultItem['name']];
                }
            }
            unset($defaultItem); // ✅ Putus reference
        }

        return $defaults;
    }

    public function hasPermission(string $module, ?string $item = null): bool
    {
        $permissions = $this->getMergedPermissions();

        if (!isset($permissions[$module])) {
            return false;
        }

        $moduleData = $permissions[$module];

        // Jika module tidak aktif, langsung false
        if (!$moduleData['enabled']) {
            return false;
        }

        // Jika hanya cek module (tanpa item spesifik)
        if ($item === null) {
            return true;
        }

        // Cek item spesifik
        foreach ($moduleData['items'] ?? [] as $pItem) {
            if ($pItem['name'] === $item) {
                return (bool) $pItem['enabled'];
            }
        }

        return false;
    }
}
