<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\Paket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_diakses(): void
    {
        $this->get('/login')->assertOk()->assertSee('Selamat Datang');
    }

    public function test_login_dengan_kredensial_benar_berhasil(): void
    {
        $user = User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login', ['username' => 'admin', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_password_salah_ditolak(): void
    {
        User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
        ]);

        $this->post('/login', ['username' => 'admin', 'password' => 'salahsekali'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_username_tidak_ada_ditolak(): void
    {
        $this->post('/login', ['username' => 'entah', 'password' => 'apa-saja'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_input_kosong_ditolak(): void
    {
        $this->post('/login', ['username' => '', 'password' => ''])
            ->assertSessionHasErrors(['username', 'password']);
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/transaksi')->assertRedirect(route('login'));
        $this->get('/member')->assertRedirect(route('login'));
        $this->get('/laporan')->assertRedirect(route('login'));
    }

    public function test_aktivitas_login_berhasil_tercatat(): void
    {
        User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
        ]);

        $this->post('/login', ['username' => 'admin', 'password' => 'rahasia123']);

        $this->assertDatabaseHas('tb_log', ['aksi' => 'LOGIN', 'modul' => 'auth']);
    }

    public function test_aktivitas_login_gagal_tercatat(): void
    {
        $this->post('/login', ['username' => 'hantu', 'password' => 'salah']);

        $this->assertDatabaseHas('tb_log', ['aksi' => 'LOGIN']);
        $this->assertStringContainsString('Gagal login', LogAktivitas::first()->deskripsi);
    }

    public function test_logout_berhasil(): void
    {
        $user = User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
        ]);

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_username_duplikat_ditolak(): void
    {
        User::create([
            'nama' => 'Pertama',
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
        ]);

        $adminLain = User::create([
            'nama' => 'Kedua',
            'username' => 'admin2',
            'password' => 'rahasia123',
            'role' => 'admin',
        ]);

        $this->actingAs($adminLain)->post(route('user.store'), [
            'nama' => 'Ketiga',
            'username' => 'admin',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'kasir',
        ])->assertSessionHasErrors('username');
    }
}