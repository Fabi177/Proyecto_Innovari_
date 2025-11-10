<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos relacionados con la gestión de pacientes
        Permission::create(['name' => 'ver pacientes']);
        Permission::create(['name' => 'crear pacientes']);
        Permission::create(['name' => 'editar pacientes']);
        Permission::create(['name' => 'eliminar pacientes']);

        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Asignar permisos al rol de admin (acceso completo)
        $adminRole->givePermissionTo([
            'ver pacientes',
            'crear pacientes',
            'editar pacientes',
            'eliminar pacientes'
        ]);

        // Asignar solo el permiso de "ver pacientes" al rol de usuario
        $userRole->givePermissionTo('ver pacientes');
    }
}
