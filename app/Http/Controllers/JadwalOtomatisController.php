<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalOtomatisController extends Controller
{
    public function step1()
    {
        // Dummy Data for Step 1
        // Context: "Informatika, Sistem Informasi, Rekayasa Komputer"
        $prodis = [
            (object)['id' => 1, 'nama' => 'S1 Informatika'],
            (object)['id' => 2, 'nama' => 'S1 Sistem Informasi'],
            (object)['id' => 3, 'nama' => 'S1 Rekayasa Komputer'],
        ];

        // Kurikulum Dummy
        $kurikulums = [
            '2023' => [
                'semesters' => [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            '2025' => [
                'semesters' => [1, 2, 3, 4, 5, 6, 7, 8]
            ]
        ];

        return view('penjadwalan.otomatis.step1', [
            'prodis' => $prodis,
            'kurikulums' => $kurikulums
        ]);
    }

    public function storeStep1(Request $request)
    {
        // Validation (Make sure at least one semester is selected if needed, or just nullable)
        // For now just storing to session
        session(['wizard_step1' => $request->all()]);

        return redirect()->route('jadwal.otomatis.step2');
    }

    public function step2()
    {
        // Dummy Data for Rooms
        $gedungs = [
            'Gedung B' => [
                'Lt 1' => ['Lab 5', 'Lab 6'],
                'Lt 2' => ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'],
            ],
            'Gedung C' => [
                'Lt 2' => ['C 2.1', 'C 2.2', 'C 2.3'],
                'Lt 3' => ['C 3.1', 'C 3.2', 'C 3.3', 'C 3.4'],
                'Lt 4' => ['C 4.1', 'Lab 7', 'Lab 8'],
            ]
        ];

        return view('penjadwalan.otomatis.step2', compact('gedungs'));
    }

    public function storeStep2(Request $request)
    {
        session(['wizard_step2' => $request->all()]);
        return redirect()->route('jadwal.otomatis.step3');
    }

    public function step3()
    {
        // Get Step 1 data
        $step1 = session('wizard_step1');

        if (!$step1) {
            return redirect()->route('jadwal.otomatis.step1');
        }

        $getSubjects = function ($master, $selectedSemesters) {
            $filtered = [];
            if(isset($selectedSemesters['kurikulum'])) {
                foreach ($selectedSemesters['kurikulum'] as $year => $semesters) {
                    foreach ($master as $sub) {
                        if ($sub['kurikulum'] == $year && in_array($sub['semester'], $semesters)) {
                            $filtered[] = (object) $sub;
                        }
                    }
                }
            }
            return $filtered;
        };

        $masterSubjects = $this->getMasterSubjects();

        $subjectsByProdi = [];
        // Map ID from step 1 checkbox/input to Name key in masterSubjects
        // Assuming step 1 input might just be implicit, but looking at step1 blade it sends 'kurikulum' array.
        // It doesn't seem to send 'prodi'. But the previous code assumed prodi input. 
        // Let's assume for this specific user request context we show all relevant prodis or defaults.
        // The previous step3 code had explicit keys. let's stick to that.
        
        $subjectsByProdi = [
            'Informatika' => $getSubjects($masterSubjects['Informatika'], $step1),
            'Sistem Informasi' => $getSubjects($masterSubjects['Sistem Informasi'], $step1),
            'Rekayasa Komputer' => $getSubjects($masterSubjects['Rekayasa Komputer'], $step1),
        ];

        $dosens = \App\Models\Dosen::with('prodi')->get();

        return view('penjadwalan.otomatis.step3', compact('subjectsByProdi', 'dosens'));
    }

    public function storeStep3(Request $request)
    {
        // $request->data structure: [ProdiName => [SubjectCode => ['classes' => [], 'lecturer' => id]]]
        session(['wizard_step3' => $request->data]);
        return redirect()->route('jadwal.otomatis.step4');
    }

    public function step4()
    {
        $step1 = session('wizard_step1');
        $step2 = session('wizard_step2');
        $step3 = session('wizard_step3');

        if (!$step1 || !$step3) { // Step 2 might be optional in logic effectively but let's assume flow
             return redirect()->route('jadwal.otomatis.step1');
        }

        // Prepare Master Data for lookup
        $masterSubjects = $this->getMasterSubjects();
        $dosens = \App\Models\Dosen::with('prodi')->get()->keyBy('id');
        
        // Flatten available rooms from step2 structure: Gedung -> Lt -> [Rooms]
        $availableRooms = [];
        if (isset($step2['rooms'])) {
             // If step2 stores flat array
             $availableRooms = $step2['rooms'];
        } else {
             // Fallback dummy
             $availableRooms = ['Lab 1', 'Lab 2', 'Lab 5', 'C 3.3']; 
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $times = ['08.00 - 09.40', '10.00 - 11.40', '13.10-14.50', '15.30 - 17.10'];
        $types = ['Teori', 'Praktikum'];

        $schedule = [];
        $no = 1;

        foreach ($step3 as $prodi => $subjects) {
            $prodiSubjects = collect($masterSubjects[$prodi] ?? [])->keyBy('code');

            foreach ($subjects as $code => $data) {
                $subjectInfo = $prodiSubjects[$code] ?? null;
                $lecturerId = $data['lecturer'] ?? null;
                $lecturerName = $dosens[$lecturerId]->name ?? 'Belum dipilih';
                $classes = $data['classes'] ?? [];

                // Filter out empty class names
                if(is_array($classes)) {
                    $classes = array_filter($classes, fn($c) => !empty($c));
                    
                    foreach ($classes as $className) {
                        $schedule[] = [
                            'no' => $no++,
                            'prodi' => $prodi,
                            'subject_name' => $subjectInfo ? $subjectInfo['name'] : $code,
                            'subject_code' => $code,
                            'semester' => $subjectInfo ? $subjectInfo['semester'] : '-',
                            'kurikulum' => $subjectInfo ? $subjectInfo['kurikulum'] : '-',
                            'lecturer' => $lecturerName,
                            'class_code' => $className,
                            'day' => $days[array_rand($days)],
                            'time' => $times[array_rand($times)],
                            'room' => !empty($availableRooms) ? $availableRooms[array_rand($availableRooms)] : 'Lab 1',
                            'type' => $types[array_rand($types)],
                        ];
                    }
                }
            }
        }

        return view('penjadwalan.otomatis.step4', compact('schedule'));
    }

    private function getMasterSubjects()
    {
        $baseSubjects = [
            'Informatika' => [
                ['code' => 'IF101', 'name' => 'Pemrograman Dasar', 'semester' => 1],
                ['code' => 'IF102', 'name' => 'Matematika Diskrit', 'semester' => 1],
                ['code' => 'IF201', 'name' => 'Algoritma & Struktur Data', 'semester' => 2],
                ['code' => 'IF301', 'name' => 'Pemrograman Berorientasi Objek', 'semester' => 3],
                ['code' => 'IF401', 'name' => 'Kecerdasan Buatan', 'semester' => 4],
                ['code' => 'IF501', 'name' => 'Pemrograman Web', 'semester' => 5],
                ['code' => 'IF601', 'name' => 'Pembelajaran Mesin', 'semester' => 6],
            ],
            'Sistem Informasi' => [
                ['code' => 'SI101', 'name' => 'Dasar Sistem Informasi', 'semester' => 1],
                ['code' => 'SI102', 'name' => 'Manajemen & Organisasi', 'semester' => 1],
                ['code' => 'SI201', 'name' => 'Analisis Proses Bisnis', 'semester' => 2],
                ['code' => 'SI301', 'name' => 'Desain Basis Data', 'semester' => 3],
                ['code' => 'SI401', 'name' => 'Manajemen Proyek TI', 'semester' => 4],
                ['code' => 'SI501', 'name' => 'E-Business', 'semester' => 5],
            ],
            'Rekayasa Komputer' => [
                ['code' => 'RK101', 'name' => 'Fisika Dasar', 'semester' => 1],
                ['code' => 'RK102', 'name' => 'Rangkaian Listrik', 'semester' => 1],
                ['code' => 'RK201', 'name' => 'Elektronika Digital', 'semester' => 2],
                ['code' => 'RK301', 'name' => 'Sistem Tertanam', 'semester' => 3],
                ['code' => 'RK401', 'name' => 'Jaringan Komputer', 'semester' => 4],
                ['code' => 'RK501', 'name' => 'Robotika', 'semester' => 5],
            ]
        ];

        $master = [];
        foreach ($baseSubjects as $prodi => $subjects) {
            foreach ($subjects as $sub) {
                // Add for 2023
                $sub2023 = $sub;
                $sub2023['kurikulum'] = '2023';
                $master[$prodi][] = $sub2023;

                // Add for 2025
                $sub2025 = $sub;
                $sub2025['kurikulum'] = '2025';
                $master[$prodi][] = $sub2025;
            }
        }

        return $master;
    }
}
