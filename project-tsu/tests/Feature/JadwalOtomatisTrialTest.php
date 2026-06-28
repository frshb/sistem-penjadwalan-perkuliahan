<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\TahunAkademik;
use App\Models\JadwalTrial;
use App\Models\Jadwal;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Models\Gedung;
use App\Models\Ruangan;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\Kelas;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JadwalOtomatisTrialTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected TahunAkademik $ta;
    protected $prodi;
    protected $dosen;
    protected $gedung;
    protected $ruangan;
    protected $kurikulum;
    protected $matkul1;
    protected $matkul2;
    protected $kelas1;
    protected $kelas2;
    protected $kelasDummy;

    protected function setUp(): void
    {
        parent::setUp();

        // Deactivate all existing academic years
        TahunAkademik::query()->update(['status_aktif' => 0]);

        // Ensure roles exist
        Role::updateOrCreate(['id_role' => 1], ['nama_role' => 'admin']);

        // Create test TahunAkademik
        $this->ta = TahunAkademik::updateOrCreate(
            ['id_tahunakademik' => 999],
            [
                'nama_tahunakademik' => '2025/2026 Gasal Test',
                'tahun_ajaran' => '2025/2026',
                'status_aktif' => 1
            ]
        );

        // Create Admin User
        $this->admin = User::create([
            'id_user' => 9999,
            'username' => 'test_admin_trial',
            'password_hash' => bcrypt('password'),
            'id_role' => 1,
        ]);

        // Create Prodi safely
        $this->prodi = Prodi::firstOrCreate(
            ['kode_prodi' => 'INF'],
            ['nama_prodi' => 'Informatika']
        );

        // Create Dosen safely
        $this->dosen = Dosen::firstOrCreate(
            ['nuptk' => '123456'],
            [
                'nama_dosen' => 'Test Lecturer',
                'nidn' => '654321',
                'id_prodi' => $this->prodi->id_prodi
            ]
        );

        // Create Gedung safely
        $this->gedung = Gedung::firstOrCreate(
            ['nama_gedung' => 'Gedung A'],
            [
                'lantai' => 3,
                'lokasi' => 'Kampus 1',
            ]
        );

        // Create Ruangan safely
        $this->ruangan = Ruangan::firstOrCreate(
            ['nama_ruang' => 'Ruang 101'],
            [
                'kapasitas' => 40,
                'fasilitas' => 'AC, Projector',
                'id_gedung' => $this->gedung->id_gedung,
                'tipe_ruangan' => 'reguler',
            ]
        );

        // Create Kurikulum safely
        $this->kurikulum = Kurikulum::firstOrCreate(
            ['kode_kurikulum' => 'K21'],
            [
                'nama_kurikulum' => 'Kurikulum 2021',
                'tahun_berlaku' => 2021,
                'status' => 'aktif',
            ]
        );

        // Create MataKuliah safely
        $this->matkul1 = MataKuliah::firstOrCreate(
            ['kode_matkul' => 'INF101'],
            [
                'nama_matkul' => 'Dasar Pemrograman',
                'sks' => 3,
                'jenis' => 'teori',
                'id_prodi' => $this->prodi->id_prodi,
                'semester' => 1,
                'id_kurikulum' => $this->kurikulum->id_kurikulum,
            ]
        );

        $this->matkul2 = MataKuliah::firstOrCreate(
            ['kode_matkul' => 'INF202'],
            [
                'nama_matkul' => 'Struktur Data',
                'sks' => 2,
                'jenis' => 'teori',
                'id_prodi' => $this->prodi->id_prodi,
                'semester' => 2,
                'id_kurikulum' => $this->kurikulum->id_kurikulum,
            ]
        );

        // Create Kelas safely
        $this->kelas1 = Kelas::firstOrCreate(
            ['nama_kelas' => 'IF-1A', 'id_tahunakademik' => 999],
            [
                'id_prodi' => $this->prodi->id_prodi,
                'kapasitas' => 30,
                'semester' => 1,
                'kode_matkul' => 'INF101',
                'id_dosen' => $this->dosen->id_dosen,
                'jumlah_mahasiswa' => 30,
            ]
        );

        $this->kelas2 = Kelas::firstOrCreate(
            ['nama_kelas' => 'IF-2A', 'id_tahunakademik' => 999],
            [
                'id_prodi' => $this->prodi->id_prodi,
                'kapasitas' => 30,
                'semester' => 2,
                'kode_matkul' => 'INF202',
                'id_dosen' => $this->dosen->id_dosen,
                'jumlah_mahasiswa' => 30,
            ]
        );

        $this->kelasDummy = Kelas::firstOrCreate(
            ['nama_kelas' => 'IF-Dummy', 'id_tahunakademik' => 999],
            [
                'id_prodi' => $this->prodi->id_prodi,
                'kapasitas' => 30,
                'semester' => 1,
                'kode_matkul' => 'INF101',
                'id_dosen' => $this->dosen->id_dosen,
                'jumlah_mahasiswa' => 30,
            ]
        );
    }

    public function test_it_can_save_trial_run()
    {
        $response = $this->actingAs($this->admin)->post(route('jadwal.otomatis.simpan_trial'), [
            'tahun_akademik_id' => $this->ta->id_tahunakademik,
            'label' => 'Test Uji Coba #1',
            'fitness' => 95.5,
            'generasi' => 100,
            'total_kelas' => 10,
            'dosen_conflicts' => 0,
            'ruangan_conflicts' => 0,
            'soft_violations' => 2,
            'jadwal_json' => json_encode([
                [
                    'kelas_id' => $this->kelas1->id_kelas,
                    'kode_mk' => 'INF101',
                    'dosen_id' => $this->dosen->id_dosen,
                    'hari_id' => 1,
                    'slot_id' => 1,
                    'ruangan_id' => $this->ruangan->id_ruang,
                    'sks' => 3
                ]
            ]),
        ]);

        $response->assertRedirect(route('jadwal.otomatis.compare_trials', ['tahun_akademik_id' => $this->ta->id_tahunakademik]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_trials', [
            'id_tahunakademik' => $this->ta->id_tahunakademik,
            'label' => 'Test Uji Coba #1',
            'fitness' => 95.5,
        ]);
    }

    public function test_it_can_view_trial_comparison()
    {
        JadwalTrial::create([
            'id_tahunakademik' => $this->ta->id_tahunakademik,
            'label' => 'Uji Coba A',
            'fitness' => 90.0,
            'generasi' => 50,
            'total_kelas' => 5,
            'dosen_conflicts' => 0,
            'ruangan_conflicts' => 0,
            'soft_violations' => 1,
            'jadwal_json' => '[]',
        ]);

        $response = $this->actingAs($this->admin)->get(route('jadwal.otomatis.compare_trials', [
            'tahun_akademik_id' => $this->ta->id_tahunakademik
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('trials');
        $response->assertSee('Uji Coba A');
    }

    public function test_it_can_apply_trial_run()
    {
        $trial = JadwalTrial::create([
            'id_tahunakademik' => $this->ta->id_tahunakademik,
            'label' => 'Uji Coba B',
            'fitness' => 99.0,
            'generasi' => 200,
            'total_kelas' => 1,
            'dosen_conflicts' => 0,
            'ruangan_conflicts' => 0,
            'soft_violations' => 0,
            'jadwal_json' => json_encode([
                [
                    'kelas_id' => $this->kelas2->id_kelas,
                    'kode_mk' => 'INF202',
                    'dosen_id' => $this->dosen->id_dosen,
                    'hari_id' => 2,
                    'slot_id' => 3,
                    'ruangan_id' => $this->ruangan->id_ruang,
                    'sks' => 2
                ]
            ]),
        ]);

        // Put some dummy existing active schedule
        Jadwal::create([
            'id_kelas' => $this->kelasDummy->id_kelas,
            'kode_matkul' => 'INF101',
            'id_dosen' => $this->dosen->id_dosen,
            'id_hari' => 1,
            'id_slot_mulai' => 1,
            'id_ruang' => $this->ruangan->id_ruang,
            'durasi_sks' => 3,
            'id_tahunakademik' => $this->ta->id_tahunakademik,
            'is_manual' => 0,
        ]);

        $response = $this->actingAs($this->admin)->post(route('jadwal.otomatis.apply_trial', $trial->id));

        $response->assertRedirect(route('jadwal.manual', ['tahun' => $this->ta->id_tahunakademik]));
        $response->assertSessionHas('success');

        // Old schedule should be cleared
        $this->assertDatabaseMissing('jadwal', [
            'id_kelas' => $this->kelasDummy->id_kelas,
            'id_tahunakademik' => $this->ta->id_tahunakademik,
        ]);

        // New trial schedule should be written
        $this->assertDatabaseHas('jadwal', [
            'id_kelas' => $this->kelas2->id_kelas,
            'kode_matkul' => 'INF202',
            'id_tahunakademik' => $this->ta->id_tahunakademik,
        ]);
    }

    public function test_it_can_delete_trial_run()
    {
        $trial = JadwalTrial::create([
            'id_tahunakademik' => $this->ta->id_tahunakademik,
            'label' => 'Uji Coba C',
            'fitness' => 80.0,
            'generasi' => 10,
            'total_kelas' => 2,
            'dosen_conflicts' => 2,
            'ruangan_conflicts' => 2,
            'soft_violations' => 5,
            'jadwal_json' => '[]',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('jadwal.otomatis.delete_trial', $trial->id));

        $response->assertRedirect(route('jadwal.otomatis.compare_trials', ['tahun_akademik_id' => $this->ta->id_tahunakademik]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('jadwal_trials', [
            'id' => $trial->id,
        ]);
    }
}
