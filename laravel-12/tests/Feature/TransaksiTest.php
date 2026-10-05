<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Outlet;
use App\Models\Paket;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, ?int $outletId = null): User
    {
        if ($role !== 'admin' && ! $outletId) {
            $outletId = Outlet::create(['nama_outlet' => 'Toko '.$role])->id_outlet;
        }

        return User::create([
            'nama' => 'Uji '.$role,
            'username' => 'uji'.$role,
            'password' => 'rahasia123',
            'role' => $role,
            'is_active' => true,
            'id_outlet' => $outletId,
        ]);
    }

    public function test_kasir_dapat_membuat_transaksi_dengan_perhitungan_benar(): void
    {
        $kasir = $this->user('kasir');
        $member = Member::create(['id_outlet' => $kasir->id_outlet, 'kode_member' => 'MBR0001', 'nama' => 'Budi', 'status' => 'aktif']);

        $paketKg = Paket::create(['id_outlet' => $kasir->id_outlet, 'nama_paket' => 'Cuci Satuan', 'jenis' => 'reguler', 'satuan' => 'kg', 'harga' => 7000, 'estimasi_hari' => 2, 'status' => 'aktif']);
        $paketPcs = Paket::create(['id_outlet' => $kasir->id_outlet, 'nama_paket' => 'Jaket', 'jenis' => 'khusus', 'satuan' => 'pcs', 'harga' => 35000, 'estimasi_hari' => 4, 'status' => 'aktif']);

        // 3 kg x 7000 = 21000, 1 pcs x 35000 = 35000 -> subtotal 56000
        $response = $this->actingAs($kasir)->post(route('transaksi.store'), [
            'id_member' => $member->id_member,
            'uang_diterima' => 100000,
            'metode_bayar' => 'tunai',
            'status' => 'proses',
            'diskon_persen' => 5,
            'pajak_persen' => 11,
            'items' => [
                ['id_paket' => $paketKg->id_paket, 'qty' => 3],
                ['id_paket' => $paketPcs->id_paket, 'qty' => 1],
            ],
        ]);

        $trx = Transaksi::first();
        $this->assertNotNull($trx);
        $response->assertRedirect(route('transaksi.show', $trx));

        $this->assertSame(56000.0, (float) $trx->total_harga);
        $this->assertSame(2800.0, (float) $trx->diskon);            // 5% dari 56000
        $this->assertSame(5852.0, (float) $trx->pajak);             // 11% dari (56000-2800) = 53200
        $this->assertSame(59052.0, (float) $trx->total_bayar);      // 56000 - 2800 + 5852
        $this->assertSame(40948.0, (float) $trx->total_kembali);    // 100000 - 59052
        $this->assertCount(2, $trx->details);

        // Poin member = floor(56000 / 100) = 560
        $this->assertSame(560, (int) $member->fresh()->poin);
    }

    public function test_nomor_invoice_dihasilkan_otomatis_dan_unik(): void
    {
        $kasir = $this->user('kasir');
        $paket = Paket::create(['id_outlet' => $kasir->id_outlet, 'nama_paket' => 'Cuci', 'jenis' => 'reguler', 'satuan' => 'kg', 'harga' => 1000, 'estimasi_hari' => 1, 'status' => 'aktif']);

        $payload = [
            'uang_diterima' => 5000,
            'metode_bayar' => 'tunai',
            'status' => 'proses',
            'diskon_persen' => 0,
            'pajak_persen' => 0,
            'items' => [['id_paket' => $paket->id_paket, 'qty' => 2]],
        ];

        $this->actingAs($kasir)->post(route('transaksi.store'), $payload);
        $this->actingAs($kasir)->post(route('transaksi.store'), $payload);

        $noInvoice = Transaksi::pluck('no_invoice')->all();
        $this->assertCount(2, $noInvoice);
        $this->assertCount(2, array_unique($noInvoice));
        $this->assertStringStartsWith('INV-'.now()->format('Ymd'), $noInvoice[0]);
    }

    public function test_harga_paket_diambil_dari_server_bukan_dari_input_pengguna(): void
    {
        $kasir = $this->user('kasir');
        $paket = Paket::create(['id_outlet' => $kasir->id_outlet, 'nama_paket' => 'Cuci', 'jenis' => 'reguler', 'satuan' => 'kg', 'harga' => 8000, 'estimasi_hari' => 1, 'status' => 'aktif']);

        $this->actingAs($kasir)->post(route('transaksi.store'), [
            'uang_diterima' => 10000,
            'metode_bayar' => 'tunai',
            'status' => 'proses',
            'diskon_persen' => 0,
            'pajak_persen' => 0,
            'items' => [['id_paket' => $paket->id_paket, 'qty' => 1, 'harga_satuan' => 1, 'subtotal' => 1]],
        ]);

        $trx = Transaksi::first();
        $this->assertSame(8000.0, (float) $trx->total_harga);
        $this->assertSame(8000.0, (float) $trx->details->first()->harga_satuan);
    }

    public function test_transaksi_tanpa_item_ditolak(): void
    {
        $kasir = $this->user('kasir');

        $this->actingAs($kasir)->post(route('transaksi.store'), [
            'uang_diterima' => 10000,
            'metode_bayar' => 'tunai',
            'status' => 'proses',
            'items' => [],
        ])->assertSessionHasErrors(['items']);

        $this->assertSame(0, Transaksi::count());
    }

    public function test_status_transaksi_dapat_diubah(): void
    {
        $kasir = $this->user('kasir');
        $trx = Transaksi::create([
            'no_invoice' => 'INV-TEST-1', 'id_outlet' => $kasir->id_outlet, 'total_harga' => 1000, 'total_bayar' => 1000,
            'metode_bayar' => 'tunai', 'status' => 'proses',
        ]);

        $this->actingAs($kasir)->patch(route('transaksi.status', $trx), ['status' => 'diambil'])
            ->assertSessionHas('success');

        $this->assertSame('diambil', $trx->fresh()->status);
        $this->actingAs($kasir)->patch(route('transaksi.status', $trx), ['status' => 'dibatalkan'])
            ->assertSessionHas('success');
        $this->assertSame('dibatalkan', $trx->fresh()->status);
    }

    public function test_kasir_dapat_membuat_transaksi_dan_mengelola_member(): void
    {
        $kasir = $this->user('kasir');

        $this->actingAs($kasir)->get(route('transaksi.create'))->assertOk();
        $this->actingAs($kasir)->get(route('member.index'))->assertOk();
        $this->actingAs($kasir)->post(route('member.store'), [
            'nama' => 'Pelanggan Kasir',
            'status' => 'aktif',
        ])->assertRedirect(route('member.index'));

        $this->assertDatabaseHas('tb_member', ['nama' => 'Pelanggan Kasir']);
        $this->actingAs($kasir)->get(route('log.index'))->assertRedirect(route('dashboard'));
    }

    public function test_owner_membuat_uid_kasir_mengganti_password_dan_kasir_login_dengan_uid(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)->post(route('kasir.store'), [
            'nama' => 'Kasir Pagi',
            'email' => 'kasirpagi@example.test',
            'password' => 'password-awal-123',
            'password_confirmation' => 'password-awal-123',
        ])->assertRedirect(route('kasir.index'));

        $kasir = User::where('email', 'kasirpagi@example.test')->firstOrFail();
        $this->assertMatchesRegularExpression('/^KSR-[A-Z0-9]{8}$/', $kasir->username);
        $this->assertSame($owner->id_outlet, $kasir->id_outlet);
        $this->assertTrue(Hash::check('password-awal-123', $kasir->password));

        $this->actingAs($owner)->put(route('kasir.update', $kasir), [
            'nama' => 'Kasir Pagi',
            'email' => 'kasirpagi@example.test',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
            'is_active' => '1',
        ])->assertRedirect(route('kasir.index'));

        $this->assertTrue(Hash::check('password-baru-123', $kasir->fresh()->password));

        $this->post(route('logout'));
        $this->post(route('login'), [
            'username' => $kasir->username,
            'password' => 'password-baru-123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_owner_tidak_dapat_mengakses_kasir_atau_transaksi_toko_lain(): void
    {
        $owner = $this->user('owner');
        $outletLain = Outlet::create(['nama_outlet' => 'Toko Lain']);
        $kasirLain = $this->user('kasir', $outletLain->id_outlet);
        $transaksiLain = Transaksi::create([
            'no_invoice' => 'INV-TOKO-LAIN',
            'id_outlet' => $outletLain->id_outlet,
            'total_harga' => 1000,
            'total_bayar' => 1000,
            'metode_bayar' => 'tunai',
            'status' => 'proses',
        ]);

        $this->actingAs($owner)->get(route('kasir.edit', $kasirLain))->assertNotFound();
        $this->actingAs($owner)->get(route('transaksi.show', $transaksiLain))->assertNotFound();
    }

    public function test_owner_dapat_mengubah_deskripsi_tokonya(): void
    {
        $owner = $this->user('owner');
        $outlet = Outlet::findOrFail($owner->id_outlet);

        $this->actingAs($owner)->put(route('toko.update'), [
            'nama_outlet' => $outlet->nama_outlet,
            'deskripsi' => 'Laundry kiloan dengan layanan antar jemput.',
            'alamat' => 'Jl. Melati 1',
            'telepon' => '08123456789',
            'email' => 'toko@example.test',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('tb_outlet', [
            'id_outlet' => $outlet->id_outlet,
            'deskripsi' => 'Laundry kiloan dengan layanan antar jemput.',
        ]);
    }

    public function test_owner_mengelola_tokonya_dan_admin_tidak_mengakses_transaksi(): void
    {
        $owner = $this->user('owner');
        $admin = $this->user('admin');

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('laporan.index'))->assertOk();
        $this->actingAs($owner)->get(route('laporan.transaksi'))->assertOk();
        $this->actingAs($owner)->get(route('laporan.pendapatan'))->assertOk();
        $this->actingAs($owner)->get(route('transaksi.index'))->assertOk();
        $this->actingAs($owner)->get(route('transaksi.create'))->assertOk();
        $this->actingAs($owner)->get(route('paket.index'))->assertOk();
        $this->actingAs($owner)->get(route('kasir.index'))->assertOk();
        $this->actingAs($owner)->get(route('toko.edit'))->assertOk();
        $this->actingAs($owner)->get(route('log.index'))->assertRedirect(route('dashboard'));

        $this->actingAs($admin)->get(route('transaksi.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->get(route('paket.index'))->assertRedirect(route('dashboard'));
    }

    public function test_akun_nonaktif_tidak_bisa_mengakses(): void
    {
        $kasir = $this->user('kasir');
        $kasir->update(['is_active' => false]);

        $this->actingAs($kasir)->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_pencarian_member_aman_dari_sql_injection(): void
    {
        $owner = $this->user('owner');
        Member::create(['id_outlet' => $owner->id_outlet, 'kode_member' => 'MBR0001', 'nama' => 'Andi', 'status' => 'aktif']);
        Member::create(['id_outlet' => $owner->id_outlet, 'kode_member' => 'MBR0002', 'nama' => 'Bunga', 'status' => 'aktif']);

        $response = $this->actingAs($owner)->get(route('api.member-cari', ['q' => "' OR '1'='1"]));

        $response->assertOk();
        $this->assertCount(0, $response->json());
        $this->assertSame(2, Member::count());
    }
}