<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\RolesAndPermissionsSeeder; // Asegúrate que esta línea esté

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            
            RolesAndPermissionsSeeder::class,
            EmpleadosSeeder::class,
            UsuariosSeeder::class,
           
        ]);
    }
}