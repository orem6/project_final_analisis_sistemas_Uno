<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if ($tenant === null) {
            return;
        }

        $users = [
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin',
                'email' => 'admin@hospital.com',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dr. García',
                'email' => 'medico@hospital.com',
                'password' => Hash::make('password'),
                'role' => 'Médico',
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Enf. López',
                'email' => 'enfermera@hospital.com',
                'password' => Hash::make('password'),
                'role' => 'Enfermera',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::query()->firstOrCreate(
                ['email' => $userData['email'], 'tenant_id' => $userData['tenant_id']],
                $userData
            );

            $user->assignRole($role);
        }

        $this->command?->info('Usuarios de prueba creados.');
    }
}
