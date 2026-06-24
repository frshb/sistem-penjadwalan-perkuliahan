<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
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

        // Ensure a test prodi and dosen exist
        Prodi::updateOrCreate(
            ['id_prodi' => 1],
            [
                'nama_prodi' => 'Informatika',
                'kode_prodi' => 'INF',
            ]
        );

        Dosen::updateOrCreate(
            ['id_dosen' => 1],
            [
                'nama_dosen' => 'Test Lecturer',
                'nuptk' => '123456',
                'nidn' => '654321',
                'id_prodi' => 1
            ]
        );
    }

    public function test_registering_dekan_requires_dosen(): void
    {
        $response = $this->from(route('users.register'))
            ->post(route('users.store'), [
                'username' => 'new_dekan',
                'password' => 'password123',
                'id_role' => 3, // Dekan
                'id_dosen' => null, // Left empty
            ]);

        $response->assertRedirect(route('users.register'));
        $response->assertSessionHasErrors(['id_dosen']);
    }

    public function test_registering_dosen_requires_dosen(): void
    {
        $response = $this->from(route('users.register'))
            ->post(route('users.store'), [
                'username' => 'new_dosen',
                'password' => 'password123',
                'id_role' => 4, // Dosen
                'id_dosen' => null, // Left empty
            ]);

        $response->assertRedirect(route('users.register'));
        $response->assertSessionHasErrors(['id_dosen']);
    }

    public function test_registering_kaprodi_requires_prodi(): void
    {
        $response = $this->from(route('users.register'))
            ->post(route('users.store'), [
                'username' => 'new_kaprodi',
                'password' => 'password123',
                'id_role' => 2, // Kaprodi
                'id_prodi' => null, // Left empty
            ]);

        $response->assertRedirect(route('users.register'));
        $response->assertSessionHasErrors(['id_prodi']);
    }

    public function test_successfully_registering_dekan_linked_to_dosen(): void
    {
        $response = $this->post(route('users.store'), [
            'username' => 'dekan_berhasil',
            'password' => 'password123',
            'id_role' => 3, // Dekan
            'id_dosen' => 1,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('user', [
            'username' => 'dekan_berhasil',
            'id_role' => 3,
            'id_dosen' => 1,
        ]);
    }

    public function test_admin_registering_dekan_requires_dosen(): void
    {
        $admin = User::create([
            'id_user' => 8881,
            'username' => 'admin_test_user',
            'password_hash' => bcrypt('password'),
            'id_role' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('settings.users.create'))
            ->post(route('settings.users.store'), [
                'username' => 'admin_new_dekan',
                'password' => 'password123',
                'id_role' => 3, // Dekan
                'id_dosen' => null, // Left empty
            ]);

        $response->assertRedirect(route('settings.users.create'));
        $response->assertSessionHasErrors(['id_dosen']);
    }

    public function test_admin_successfully_registering_dekan_linked_to_dosen(): void
    {
        $admin = User::create([
            'id_user' => 8881,
            'username' => 'admin_test_user',
            'password_hash' => bcrypt('password'),
            'id_role' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('settings.users.store'), [
                'username' => 'admin_dekan_berhasil',
                'password' => 'password123',
                'id_role' => 3, // Dekan
                'id_dosen' => 1,
            ]);

        $response->assertRedirect(route('settings.users.create'));
        $this->assertDatabaseHas('user', [
            'username' => 'admin_dekan_berhasil',
            'id_role' => 3,
            'id_dosen' => 1,
        ]);
    }
}
