<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecutar los seeders de la base de datos.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Crear usuarios admin y regular
        $adminUser = User::create([
            'name' => 'Usuario Admin',
            'email' => 'admin@ejemplo.com',
            'password' => bcrypt('contraseña'),
        ]);



        // Asignar roles
        $adminRole = Role::where('name', 'admin')->first();

        $adminUser->assignRole($adminRole);

    }
}
