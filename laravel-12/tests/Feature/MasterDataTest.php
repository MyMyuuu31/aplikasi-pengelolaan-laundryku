<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Outlet;
use App\Models\Paket;
use App\Models\Transaksi;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => 'rahasia123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function owner(string $username = 'owner'): User
    {
        $outlet = Outlet::create(['nama_outlet' => 'Toko '.$username]);

        return User::create([
            'nama' => 'Owner '.$username,
            'username' => $username,
            'password' => 'rahasia123',
            'role' => 'owner',
            'id_outlet' => $outlet->id_outlet,
            'is_active' => true,
        ]);
    }

    public function test_admin_dapat_menambah_outlet(): void
    {
        $this->actingAs($this->admin())
            ->post(route('outlet.store'), [
                'nama_outlet' => 'LaundryKu Pusat',
                'alamat' => 'Jl. Merdeka No. 10',
                'telepon' => '021-5550101',
                'email' => 'pusat@laundryku.test',
            ])
            ->assertRedirect(route('outlet.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tb_outlet', ['nama_outlet' => 'LaundryKu Pusat']);
    }

    public function test_outlet_tanpa_nama_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('outlet.store'), ['nama_outlet' => ''])
            ->assertSessionHasErrors('nama_outlet');
    }

    public function test_outlet_dengan_email_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('outlet.store'), ['nama_outlet' => 'Cabang', 'email' => 'bukan-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_penghapusan_outlet_menghapus_akun_toko_tetapi_menyimpan_transaksi(): void
    {
        $admin = $this->admin();
        $outlet = Outlet::create(['nama_outlet' => 'Pusat']);
        $owner = User::create([
            'nama' => 'Owner Pusat', 'username' => 'ownerpusat', 'password' => 'password-123',
            'role' => 'owner', 'id_outlet' => $outlet->id_outlet,
        ]);
        $kasir = User::create([
            'nama' => 'Kasir Pusat', 'username' => 'kasirpusat', 'password' => 'password-123',
            'role' => 'kasir', 'id_outlet' => $outlet->id_outlet,
        ]);
        Transaksi::create([
            'no_invoice' => 'INV-1', 'id_outlet' => $outlet->id_outlet, 'total_harga' => 1000,
            'total_bayar' => 1000, 'metode_bayar' => 'tunai', 'status' => 'proses',
        ]);

        $this->actingAs($admin)->delete(route('outlet.destroy', $outlet))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tb_outlet', ['id_outlet' => $outlet->id_outlet]);
        $this->assertDatabaseMissing('tb_user', ['id_user' => $owner->id_user]);
        $this->assertDatabaseMissing('tb_user', ['id_user' => $kasir->id_user]);
        $this->assertDatabaseHas('tb_transaksi', ['no_invoice' => 'INV-1', 'id_outlet' => null]);
    }

    public function test_kode_member_dibuat_otomatis(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('member.store'), [
            'nama' => 'Andi Pratama', 'status' => 'aktif',
        ])->assertSessionHas('success');

        $this->actingAs($owner)->post(route('member.store'), [
            'nama' => 'Bunga Lestari', 'status' => 'aktif',
        ])->assertSessionHas('success');

        $this->assertSame(['MBR0001', 'MBR0002'], Member::pluck('kode_member')->all());
    }

    public function test_nomor_hp_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->owner())->post(route('member.store'), [
            'nama' => 'Test', 'no_hp' => 'nomor!!', 'status' => 'aktif',
        ])->assertSessionHasErrors('no_hp');
    }

    public function test_paket_tanpa_harga_ditolak(): void
    {
        $this->actingAs($this->owner())->post(route('paket.store'), [
            'nama_paket' => 'Cuci', 'jenis' => 'reguler', 'satuan' => 'kg',
            'harga' => '', 'estimasi_hari' => 3, 'status' => 'aktif',
        ])->assertSessionHasErrors('harga');
    }

    public function test_paket_yang_sudah_dipakai_dapat_diarsipkan_tanpa_merusak_transaksi(): void
    {
        $owner = $this->owner();
        $paket = Paket::create([
            'id_outlet' => $owner->id_outlet,
            'nama_paket' => 'Cuci', 'jenis' => 'reguler', 'satuan' => 'kg',
            'harga' => 1000, 'estimasi_hari' => 1, 'status' => 'aktif',
        ]);
        $trx = Transaksi::create([
            'no_invoice' => 'INV-2', 'total_harga' => 1000, 'total_bayar' => 1000,
            'metode_bayar' => 'tunai', 'status' => 'proses',
        ]);
        $trx->details()->create([
            'id_paket' => $paket->id_paket, 'nama_paket' => $paket->nama_paket,
            'satuan' => 'kg', 'harga_satuan' => 1000, 'qty' => 1, 'subtotal' => 1000,
        ]);

        $this->actingAs($owner)->delete(route('paket.destroy', $paket))->assertSessionHas('success');
        $this->assertDatabaseHas('tb_paket', ['id_paket' => $paket->id_paket, 'status' => 'nonaktif']);
        $this->assertDatabaseHas('tb_detail_transaksi', ['id_transaksi' => $trx->id_transaksi, 'id_paket' => $paket->id_paket]);
    }

    public function test_halaman_master_data_dapat_diakses_admin(): void
    {
        $admin = $this->admin();

        foreach (['outlet.index', 'user.index', 'log.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        foreach (['outlet.create', 'user.create'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        foreach (['member.index', 'paket.index', 'kasir.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertRedirect(route('dashboard'));
        }

        $owner = $this->owner();
        foreach (['member.index', 'paket.index', 'kasir.index', 'toko.edit'] as $route) {
            $this->actingAs($owner)->get(route($route))->assertOk();
        }
    }

    public function test_owner_terikat_toko_hanya_dihapus_bersama_tokonya(): void
    {
        $admin = $this->admin();
        $owner = $this->owner();

        $this->actingAs($admin)->delete(route('user.destroy', $owner))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tb_user', ['id_user' => $owner->id_user]);
        $this->assertDatabaseHas('tb_outlet', ['id_outlet' => $owner->id_outlet]);
    }

    public function test_laporan_operasional_hanya_dapat_diakses_owner_dan_kasir(): void
    {
        $admin = $this->admin();
        foreach (['laporan.index', 'laporan.transaksi', 'laporan.pendapatan'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertRedirect(route('dashboard'));
        }

        foreach (['kasir', 'owner'] as $role) {
            $outlet = $role === 'admin' ? null : Outlet::create(['nama_outlet' => 'Toko '.$role])->id_outlet;
            $user = User::create([
                'nama' => 'Uji '.$role, 'username' => 'uji'.$role,
                'password' => 'rahasia123', 'role' => $role, 'is_active' => true, 'id_outlet' => $outlet,
            ]);

            foreach (['laporan.index', 'laporan.transaksi', 'laporan.pendapatan', 'laporan.paket', 'laporan.member'] as $route) {
                $this->actingAs($user)->get(route($route))->assertOk();
            }
        }
    }

    public function test_aktivitas_crud_master_tercatat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('outlet.store'), ['nama_outlet' => 'Cabang Timur']);
        $outlet = Outlet::where('nama_outlet', 'Cabang Timur')->firstOrFail();
        $owner = User::create([
            'nama' => 'Owner Timur', 'username' => 'ownertimur', 'password' => 'rahasia123',
            'role' => 'owner', 'id_outlet' => $outlet->id_outlet,
        ]);
        $this->actingAs($owner)->post(route('member.store'), ['nama' => 'Sari', 'status' => 'aktif']);
        $this->actingAs($owner)->post(route('paket.store'), [
            'nama_paket' => 'Setrika', 'jenis' => 'setrika', 'satuan' => 'kg',
            'harga' => 5000, 'estimasi_hari' => 1, 'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('tb_log', ['modul' => 'outlet', 'aksi' => 'TAMBAH']);
        $this->assertDatabaseHas('tb_log', ['modul' => 'member', 'aksi' => 'TAMBAH']);
        $this->assertDatabaseHas('tb_log', ['modul' => 'paket', 'aksi' => 'TAMBAH']);
    }

    public function test_laporan_bisa_ditapis_berdasarkan_periode(): void
    {
        $owner = $this->owner();

        $lama = Transaksi::create([
            'no_invoice' => 'INV-20240101', 'id_outlet' => $owner->id_outlet, 'total_harga' => 10000, 'diskon' => 0, 'pajak' => 0,
            'total_bayar' => 10000, 'metode_bayar' => 'tunai', 'status' => 'diambil',
        ]);
        $lama->forceFill(['created_at' => now()->subMonths(2), 'updated_at' => now()->subMonths(2)])->save();

        $baru = Transaksi::create([
            'no_invoice' => 'INV-BARU', 'id_outlet' => $owner->id_outlet, 'total_harga' => 20000, 'diskon' => 0, 'pajak' => 0,
            'total_bayar' => 20000, 'metode_bayar' => 'qris', 'status' => 'diambil',
        ]);
        $baru->forceFill(['created_at' => now(), 'updated_at' => now()])->save();

$response = $this->actingAs($owner)->get(route('laporan.index', [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();

        // Hanya transaksi bulan berjalan yang dihitung: 1 transaksi bernilai 20000.
        $this->assertSame(1, $response->viewData('ringkasan')['jumlah_transaksi']);
        $this->assertSame(20000.0, $response->viewData('ringkasan')['total_diterima']);

        // Tanpa filter tanggal, laporan tetap terbatasi bulan berjalan: 1 transaksi.
        $semua = $this->actingAs($owner)->get(route('laporan.index'));
        $this->assertSame(1, $semua->viewData('ringkasan')['jumlah_transaksi']);

        // Jika periode diperlebar, transaksi lama ikut terhitung.
        $lebar = $this->actingAs($owner)->get(route('laporan.index', [
            'dari' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ]));
        $this->assertSame(2, $lebar->viewData('ringkasan')['jumlah_transaksi']);
        $this->assertSame(30000.0, $lebar->viewData('ringkasan')['total_diterima']);

        // Halaman laporan transaksi tidak memfilter tanggal secara default.
        $semuaTrx = $this->actingAs($owner)->get(route('laporan.transaksi'));
        $this->assertSame(1, $semuaTrx->viewData('transaksis')->total());

        // Halaman laporan transaksi juga membatasi periode bulan berjalan secara default.
        $perStatus = $this->actingAs($owner)->get(route('laporan.transaksi', [
            'status' => 'diambil',
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ]));
        $this->assertSame(1, $perStatus->viewData('transaksis')->total());

        $semuaDiambil = $this->actingAs($owner)->get(route('laporan.transaksi', ['status' => 'diambil']));
        $this->assertSame(1, $semuaDiambil->viewData('transaksis')->total());

        $perProses = $this->actingAs($owner)->get(route('laporan.transaksi', ['status' => 'proses']));
        $this->assertSame(0, $perProses->viewData('transaksis')->total());

        $outletLain = Outlet::create(['nama_outlet' => 'Toko Lain']);
        $tetapTokoSendiri = $this->actingAs($owner)->get(route('laporan.index', ['id_outlet' => $outletLain->id_outlet]));
        $this->assertSame(1, $tetapTokoSendiri->viewData('ringkasan')['jumlah_transaksi']);
    }

    public function test_seeder_membuat_outlet_dan_akun_role_dengan_password_terhash(): void
    {
        config()->set('laundry.seeder.nama_outlet', 'Outlet Uji');
        config()->set('laundry.seeder.users', [
            'admin' => ['nama' => 'Admin Seed', 'username' => 'admin-seed', 'password' => 'password-admin-123'],
            'kasir' => ['nama' => 'Kasir Seed', 'username' => 'kasir-seed', 'password' => 'password-kasir-123'],
            'owner' => ['nama' => 'Owner Seed', 'username' => 'owner-seed', 'password' => 'password-owner-123'],
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('username', 'admin-seed')->firstOrFail();
        $this->assertTrue(Hash::check('password-admin-123', $admin->password));
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(User::where('role', 'kasir')->exists());
        $this->assertMatchesRegularExpression('/^KSR-[A-Z0-9]{8}$/', User::where('role', 'kasir')->value('username'));
        $this->assertSame('owner', User::where('username', 'owner-seed')->value('role'));
        $this->assertDatabaseHas('tb_outlet', ['nama_outlet' => 'Outlet Uji']);

        $admin->update(['nama' => 'Admin Diubah Manual']);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('tb_user', 3);
        $this->assertDatabaseCount('tb_outlet', 1);
        $this->assertSame('Admin Diubah Manual', $admin->fresh()->nama);
    }
}