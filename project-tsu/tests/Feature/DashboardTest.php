<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::updateOrCreate(['id_role' => 1], ['nama_role' => 'admin']);
        Role::updateOrCreate(['id_role' => 2], ['nama_role' => 'kaprodi']);
        Role::updateOrCreate(['id_role' => 3], ['nama_role' => 'dekan']);
        Role::updateOrCreate(['id_role' => 4], ['nama_role' => 'dosen']);
        Role::updateOrCreate(['id_role' => 5], ['nama_role' => 'mahasiswa']);

        // Deactivate all existing academic years to avoid collision with seeded data
        TahunAkademik::query()->update(['status_aktif' => 0]);
    }

    public function test_admin_does_not_see_active_academic_year_notification(): void
    {
        // Setup active academic year
        $activeYear = TahunAkademik::updateOrCreate(
            ['id_tahunakademik' => 999],
            [
                'nama_tahunakademik' => '2025/2026 Gasal Test',
                'tahun_ajaran' => '2025/2026',
                'status_aktif' => 1
            ]
        );

        $admin = User::create([
            'id_user' => 9991,
            'username' => 'test_admin_user',
            'password_hash' => bcrypt('password'),
            'id_role' => 1, // Admin
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Penjadwalan Tahun Akademik Aktif Telah Dibuka!');
    }

    public function test_non_admin_sees_active_academic_year_notification(): void
    {
        // Setup active academic year
        $activeYear = TahunAkademik::updateOrCreate(
            ['id_tahunakademik' => 999],
            [
                'nama_tahunakademik' => '2025/2026 Gasal Test',
                'tahun_ajaran' => '2025/2026',
                'status_aktif' => 1
            ]
        );

        // Dekan user
        $dekan = User::create([
            'id_user' => 9992,
            'username' => 'test_dekan_user',
            'password_hash' => bcrypt('password'),
            'id_role' => 3, // Dekan
        ]);

        $response = $this->actingAs($dekan)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Penjadwalan Tahun Akademik Aktif Telah Dibuka!');
        $response->assertSee('2025/2026 Gasal Test');
    }

    public function test_dosen_does_not_see_active_academic_year_notification(): void
    {
        // Setup active academic year
        $activeYear = TahunAkademik::updateOrCreate(
            ['id_tahunakademik' => 999],
            [
                'nama_tahunakademik' => '2025/2026 Gasal Test',
                'tahun_ajaran' => '2025/2026',
                'status_aktif' => 1
            ]
        );

        // Dosen user
        $dosen = User::create([
            'id_user' => 9993,
            'username' => 'test_dosen_user',
            'password_hash' => bcrypt('password'),
            'id_role' => 4, // Dosen
            'id_dosen' => 1,
        ]);

        $response = $this->actingAs($dosen)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Penjadwalan Tahun Akademik Aktif Telah Dibuka!');
    }

    public function test_kaprodi_or_dekan_with_id_dosen_sees_teaching_schedule_section(): void
    {
        // Setup active academic year
        $activeYear = TahunAkademik::updateOrCreate(
            ['id_tahunakademik' => 999],
            [
                'nama_tahunakademik' => '2025/2026 Gasal Test',
                'tahun_ajaran' => '2025/2026',
                'status_aktif' => 1
            ]
        );

        // Kaprodi user who also teaches (has id_dosen)
        $kaprodiTeaches = User::create([
            'id_user' => 9994,
            'username' => 'test_kaprodi_teaches',
            'password_hash' => bcrypt('password'),
            'id_role' => 2, // Kaprodi
            'id_dosen' => 1,
        ]);

        $response = $this->actingAs($kaprodiTeaches)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Jadwal Mengajar Anda');
        $response->assertDontSee('S1 Informatika'); // Static cards hidden because they teach
    }

    public function test_non_dosen_without_id_dosen_sees_static_program_cards(): void
    {
        // Kaprodi user who does not teach (id_dosen is null)
        $kaprodi = User::create([
            'id_user' => 9995,
            'username' => 'test_kaprodi_not_teaches',
            'password_hash' => bcrypt('password'),
            'id_role' => 2, // Kaprodi
            'id_dosen' => null,
        ]);

        $response = $this->actingAs($kaprodi)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('S1 Informatika'); // Static cards visible
        $response->assertDontSee('Jadwal Mengajar Anda');
    }
}
