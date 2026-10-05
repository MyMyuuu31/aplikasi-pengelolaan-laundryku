<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = config('laundry.seeder.users');

        foreach ($users as $role => $attributes) {
            if (blank($attributes['password'] ?? null) || mb_strlen($attributes['password']) < 8) {
                throw new RuntimeException("Atur password minimal 8 karakter untuk role {$role} di file .env sebelum menjalankan db:seed.");
            }
        }

        $usernames = array_column($users, 'username');
        if (count($usernames) !== count(array_unique($usernames))) {
            throw new RuntimeException('Username default admin, kasir, dan owner harus berbeda.');
        }

        DB::transaction(function () use ($users): void {
            $outlet = Outlet::firstOrCreate(
                ['nama_outlet' => config('laundry.seeder.nama_outlet')],
                ['alamat' => 'Alamat belum diatur']
            );

            foreach ($users as $role => $attributes) {
                if ($role === 'kasir') {
                    $kasir = User::where('username', $attributes['username'])->first()
                        ?? User::where('role', 'kasir')
                            ->where('id_outlet', $outlet->id_outlet)
                            ->where('nama', $attributes['nama'])
                            ->first();

                    if (! $kasir) {
                        User::create([
                            'nama' => $attributes['nama'],
                            'username' => $this->uidKasir(),
                            'password' => Hash::make($attributes['password']),
                            'role' => 'kasir',
                            'id_outlet' => $outlet->id_outlet,
                            'is_active' => true,
                        ]);
                    } elseif (! Str::startsWith($kasir->username, 'KSR-')) {
                        $kasir->update(['username' => $this->uidKasir()]);
                    }

                    continue;
                }

                $user = User::firstOrCreate(
                    ['username' => $attributes['username']],
                    [
                        'nama' => $attributes['nama'],
                        'password' => Hash::make($attributes['password']),
                        'role' => $role,
                        'id_outlet' => in_array($role, ['owner', 'kasir'], true) ? $outlet->id_outlet : null,
                        'is_active' => true,
                    ]
                );

                if ($role === 'owner' && ! $user->id_outlet && ! User::where('role', 'owner')->where('id_outlet', $outlet->id_outlet)->exists()) {
                    $user->update(['id_outlet' => $outlet->id_outlet]);
                }
            }
        });
    }

    private function uidKasir(): string
    {
        do {
            $uid = 'KSR-'.Str::upper(Str::random(8));
        } while (User::where('username', $uid)->exists());

        return $uid;
    }
}
