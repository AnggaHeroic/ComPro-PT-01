<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Buat/perbarui akun admin dari ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD di .env.
     */
    public function run(): void
    {
        $email = config('admin.account.email');
        $password = config('admin.account.password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum diisi di .env, akun admin dilewati.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.account.name'),
                'password' => Hash::make($password),
            ],
        );
    }
}
