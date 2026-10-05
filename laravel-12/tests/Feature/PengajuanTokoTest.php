<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PengajuanToko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengajuanTokoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nama' => 'Admin Platform',
            'username' => 'admin-platform',
            'password' => 'admin-password-123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function kirimPengajuan(): PengajuanToko
    {
        $this->post(route('daftar-toko.store'), [
            'nama_owner' => 'Rani Pemilik',
            'username' => 'rani-owner',
            'email' => 'rani@example.test',
            'password' => 'rani-password-123',
            'password_confirmation' => 'rani-password-123',
            'nama_outlet' => 'Laundry Rani',
            'alamat' => 'Jl. Mawar No. 1',
            'telepon' => '08123456789',
            'deskripsi' => 'Laundry kiloan dan setrika.',
        ])->assertRedirect(route('login'));

        return PengajuanToko::firstOrFail();
    }

    public function test_pengajuan_publik_disimpan_pending_dengan_password_terhash(): void
    {
        $this->get(route('daftar-toko.create'))->assertOk();
        $pengajuan = $this->kirimPengajuan();

        $this->assertSame('menunggu', $pengajuan->status);
        $this->assertTrue(Hash::check('rani-password-123', $pengajuan->password));
        $this->assertDatabaseMissing('tb_user', ['username' => 'rani-owner']);
    }

    public function test_admin_menyetujui_pengajuan_dan_membuat_owner_serta_toko(): void
    {
        $pengajuan = $this->kirimPengajuan();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('pengajuan-toko.approve', $pengajuan))
            ->assertSessionHas('success');

        $owner = User::where('username', 'rani-owner')->firstOrFail();
        $outlet = Outlet::findOrFail($owner->id_outlet);

        $this->assertSame('owner', $owner->role);
        $this->assertSame('Laundry Rani', $outlet->nama_outlet);
        $this->assertSame('Laundry kiloan dan setrika.', $outlet->deskripsi);
        $this->assertTrue(Hash::check('rani-password-123', $owner->password));
        $this->assertDatabaseHas('tb_pengajuan_toko', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'status' => 'disetujui',
            'id_owner' => $owner->id_user,
            'id_admin' => $admin->id_user,
        ]);

        $this->post(route('login'), ['username' => 'rani-owner', 'password' => 'rani-password-123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_dapat_menolak_pengajuan_dan_owner_tidak_bisa_mengelola_antrean(): void
    {
        $pengajuan = $this->kirimPengajuan();
        $owner = User::create([
            'nama' => 'Owner Lain', 'username' => 'owner-lain', 'password' => 'password-owner-123',
            'role' => 'owner', 'id_outlet' => Outlet::create(['nama_outlet' => 'Toko Lain'])->id_outlet,
        ]);

        $this->actingAs($owner)->get(route('pengajuan-toko.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($this->admin())->post(route('pengajuan-toko.reject', $pengajuan))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tb_pengajuan_toko', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'status' => 'ditolak',
        ]);
        $this->assertDatabaseMissing('tb_user', ['username' => 'rani-owner']);
    }
}