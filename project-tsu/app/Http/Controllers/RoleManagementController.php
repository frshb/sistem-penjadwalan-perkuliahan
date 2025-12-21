<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index()
    {
        // Dummy data for ALL roles
        $rolesData = [
            'Super Admin' => [
                'management_data' => [
                    'enabled' => true,
                    'items' => [
                        ['name' => 'Management Prodi', 'enabled' => true],
                        ['name' => 'Management Ruangan', 'enabled' => true],
                        ['name' => 'Management Mata Kuliah', 'enabled' => true],
                        ['name' => 'Management Data Dosen', 'enabled' => true],
                        ['name' => 'Management Data Mahasiswa', 'enabled' => true],
                        ['name' => 'Management KP & Skripsi', 'enabled' => true],
                    ]
                ],
                'modul_penjadwalan' => [
                    'enabled' => true,
                    'items' => [
                        ['name' => 'Penjadwalan Otomatis', 'enabled' => true],
                        ['name' => 'Penjadwalan Manual', 'enabled' => true],
                    ]
                ]
            ],
            'Kaprodi' => [
                'management_data' => [
                    'enabled' => true,
                    'items' => [
                        ['name' => 'Management Prodi', 'enabled' => false],
                        ['name' => 'Management Ruangan', 'enabled' => true],
                        ['name' => 'Management Mata Kuliah', 'enabled' => true],
                        ['name' => 'Management Data Dosen', 'enabled' => true],
                        ['name' => 'Management Data Mahasiswa', 'enabled' => true],
                        ['name' => 'Management KP & Skripsi', 'enabled' => false],
                    ]
                ],
                'modul_penjadwalan' => [
                    'enabled' => true,
                    'items' => [
                        ['name' => 'Penjadwalan Otomatis', 'enabled' => true],
                        ['name' => 'Penjadwalan Manual', 'enabled' => true],
                    ]
                ]
            ],
            'Dosen' => [
                'management_data' => [
                    'enabled' => false,
                    'items' => [
                        ['name' => 'Management Prodi', 'enabled' => false],
                        ['name' => 'Management Ruangan', 'enabled' => false],
                        ['name' => 'Management Mata Kuliah', 'enabled' => false],
                        ['name' => 'Management Data Dosen', 'enabled' => false],
                        ['name' => 'Management Data Mahasiswa', 'enabled' => false],
                        ['name' => 'Management KP & Skripsi', 'enabled' => true], // Maybe for guidance
                    ]
                ],
                'modul_penjadwalan' => [
                    'enabled' => false,
                    'items' => [
                        ['name' => 'Penjadwalan Otomatis', 'enabled' => false],
                        ['name' => 'Penjadwalan Manual', 'enabled' => false],
                    ]
                ]
            ],
            'Mahasiswa' => [
                'management_data' => [
                    'enabled' => false,
                    'items' => [
                        ['name' => 'Management Prodi', 'enabled' => false],
                        ['name' => 'Management Ruangan', 'enabled' => false],
                        ['name' => 'Management Mata Kuliah', 'enabled' => false],
                        ['name' => 'Management Data Dosen', 'enabled' => false],
                        ['name' => 'Management Data Mahasiswa', 'enabled' => false],
                        ['name' => 'Management KP & Skripsi', 'enabled' => true],
                    ]
                ],
                'modul_penjadwalan' => [
                    'enabled' => false,
                    'items' => [
                        ['name' => 'Penjadwalan Otomatis', 'enabled' => false],
                        ['name' => 'Penjadwalan Manual', 'enabled' => false],
                    ]
                ]
            ]
        ];

        return view('settings.roles.index', compact('rolesData'));
    }

    public function update(Request $request)
    {
        // Placeholder for update logic
        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }
}
