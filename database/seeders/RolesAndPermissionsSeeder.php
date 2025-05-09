<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Usuario; // Asegúrate de importar tu modelo de Usuario
use App\Models\Empleado; // Asegúrate de importar tu modelo de Empleado si creas un usuario por defecto

use Illuminate\Support\Facades\DB; // Opcional: para limpiar tablas si es necesario
use Illuminate\Support\Facades\Hash; // Para hashear la contraseña del usuario por defecto
use Illuminate\Support\Facades\Log; // Para logging en seeder (opcional)


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos.
     * Define roles y permisos iniciales para la aplicacion.
     */
    public function run(): void
    {
        Log::info('Iniciando RolesAndPermissionsSeeder.');

        // Deshabilitar restricciones de clave foránea temporalmente
        // Esto permite truncar las tablas de Spatie sin conflictos
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Truncar (vaciar) las tablas de Spatie para empezar limpio cada vez que se ejecuta el seeder
        DB::table('role_has_permissions')->truncate(); // Relacion Permiso-Rol
        DB::table('model_has_roles')->truncate();      // Relacion Modelo-Rol (Usuario-Rol)
        DB::table('model_has_permissions')->truncate(); // Relacion Modelo-Permiso (Usuario-Permiso directo)
        DB::table('roles')->truncate();                // Tabla de Roles
        DB::table('permissions')->truncate();          // Tabla de Permisos
        // Habilitar restricciones de clave foránea nuevamente
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Log::info('Tablas de Spatie truncadas y listas para (re)crear roles y permisos.');

        // Reiniciar los caches de roles y permisos de Spatie
        // Es importante hacer esto despues de modificar roles/permisos en la base de datos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Log::info('Caché de permisos reiniciado.');

        // --- 1. Crear Permisos ---
        Log::info('Creando permisos...');

        // Permisos para Proveedores (CRUD)
        Permission::create(['name' => 'view suppliers']);
        Permission::create(['name' => 'create suppliers']);
        Permission::create(['name' => 'edit suppliers']);
        Permission::create(['name' => 'delete suppliers']);

        // Permisos para Empleados (CRUD)
        Permission::create(['name' => 'view employees']);
        Permission::create(['name' => 'create employees']);
        Permission::create(['name' => 'edit employees']);
        Permission::create(['name' => 'delete employees']);

        // Permisos para Movimientos de Grúas (CRUD)
        Permission::create(['name' => 'view movements']); // O 'view movimientos-grua' para ser mas especifico con la ruta
        Permission::create(['name' => 'create movements']); // O 'create movimientos-grua'
        Permission::create(['name' => 'edit movements']); // O 'edit movimientos-grua'
        Permission::create(['name' => 'delete movements']); // O 'delete movimientos-grua'

         // Permisos para Grúas (CRUD)
        Permission::create(['name' => 'view gruas']);
        Permission::create(['name' => 'create gruas']);
        Permission::create(['name' => 'edit gruas']);
        Permission::create(['name' => 'delete gruas']);

        // Permisos para Entradas/Salidas de Grúas (CRUD) - Necesarios para el rol Guardia
        Permission::create(['name' => 'view entrada-salida-gruas']);
        Permission::create(['name' => 'create entrada-salida-gruas']);
        Permission::create(['name' => 'edit entrada-salida-gruas']); // Guardia no tendra este
        Permission::create(['name' => 'delete entrada-salida-gruas']); // Guardia no tendra este


        // Permisos para Usuarios (CRUD y gestion de roles/permisos)
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        // Permiso clave para acceder a la lista de roles en UserController::getRoles()
        Permission::create(['name' => 'manage users']); // Permiso general para gestion de usuarios/roles


        // Permiso de acceso al panel de administrador (para mostrar el enlace en el menu)
        Permission::create(['name' => 'access admin panel']);

        // Permisos de acceso a secciones especificas del panel (para mostrar sub-menus o enlaces)
        Permission::create(['name' => 'access gruas section']); // <-- Creado con 'g' minuscula


        // --- Opcional: Añadir permisos para otras tablas (Inventario, Reportes, etc.) ---
        // Permission::create(['name' => 'view inventario']);
        // Permission::create(['name' => 'create inventario']);
        // Permission::create(['name' => 'edit inventario']);
        // Permission::create(['name' => 'delete inventario']);
        // Permission::create(['name' => 'view reportes']);


        Log::info('Permisos creados.');

        // --- 2. Crear Roles ---
        Log::info('Creando roles...');

        // Rol Super-Admin: Tiene control total
        $superAdminRole = Role::create(['name' => 'super-admin']);

        // Rol Admin: Gestiona la mayoria de secciones, incluyendo gestion basica de usuarios/roles
        $adminRole = Role::create(['name' => 'admin']);

        // Rol Guardia: Acceso limitado a Entradas/Salidas de Grúas
        $guardiaRole = Role::create(['name' => 'guardia']);

        // Rol Almacenista: Gestiona Proveedores
        $almacenistaRole = Role::create(['name' => 'almacenista']);

        // Rol Gruas: Gestiona Grúas y sus Movimientos
        $gruasRole = Role::create(['name' => 'gruas']);

        // Rol Segurista: Solo puede ver información
        $seguristaRole = Role::create(['name' => 'segurista']);

        // Rol Servicios Industriales: Sin permisos administrativos especificos definidos aqui
        $serviciosRole = Role::create(['name' => 'servicios-industriales']);

        // Rol Basico: Para usuarios sin roles administrativos
        $basicUserRole = Role::create(['name' => 'basic user']);


        Log::info('Roles creados.');


        // --- 3. Asignar Permisos a Roles ---
        Log::info('Asignando permisos a roles...');

        // Rol Super-Admin: Asigna todos los permisos creados a este rol
        // Como 'access gruas section' ya existe, givePermissionTo(Permission::all()) lo incluira
        $superAdminRole->givePermissionTo(Permission::all());
        Log::info("Permisos asignados al rol 'super-admin'.");

        // Rol Admin: Permisos de gestion amplia, incluyendo gestion de usuarios/roles
        $adminRole->givePermissionTo([
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view movements', 'create movements', 'edit movements', 'delete movements', // Asumiendo que movimientos-grua = movements
            'view gruas', 'create gruas', 'edit gruas', 'delete gruas',
            'view users', 'create users', 'edit users', 'delete users',
            'manage users', // Permiso necesario para ver la lista de roles y otras acciones de gestion de usuarios/roles
            'access admin panel', // Para que pueda ver el panel
            'view entrada-salida-gruas', // Admin tambien puede verlas
        ]);
         Log::info("Permisos asignados al rol 'admin'.");

        // Rol Guardia: Solo ver y crear Entradas/Salidas de Grúas, y acceso al panel
        $guardiaRole->givePermissionTo([
             'view entrada-salida-gruas',
             'create entrada-salida-gruas',
             'access admin panel', // Para que vea el enlace al panel en el menu
        ]);
        Log::info("Permisos asignados al rol 'guardia'.");


        // Rol Almacenista: Ver, crear, editar Proveedores y acceso al panel
        $almacenistaRole->givePermissionTo([
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'access admin panel', // Para que vea el enlace al panel
             'view employees',
             'view movements',
             'view gruas',
        ]);
        Log::info("Permisos asignados al rol 'almacenista'.");


        // Rol Gruas: Ver y crear Movimientos y Grúas, acceso a su seccion y al panel
        $gruasRole->givePermissionTo([
             'view movements', // Asumiendo que movimientos-grua = movements
             'create movements',
             'view gruas',
             'create gruas',
             'access gruas section', // <-- ¡CORREGIDO! 'g' minuscula para coincidir con la creacion
             'access admin panel', // Para que vea el enlace al panel
        ]);
        Log::info("Permisos asignados al rol 'gruas'.");


        // Rol Segurista: Solo puede ver información específica y acceso al panel
        $seguristaRole->givePermissionTo([
            'view suppliers',
            'view employees',
            'view movements', // Asumiendo que movimientos-grua = movements
            'view gruas',
            'view entrada-salida-gruas', // Segurista tambien puede ver movimientos especificos de entrada/salida
            'access admin panel', // Para que vea el enlace al panel
        ]);
         Log::info("Permisos asignados al rol 'segurista'.");


        // Rol Servicios Industriales: Solo acceso al panel, sin permisos CRUD definidos aqui
        $serviciosRole->givePermissionTo([
             'access admin panel',
        ]);
        Log::info("Permisos asignados al rol 'servicios-industriales'.");


        // Rol Básico: Sin permisos administrativos por defecto
        $basicUserRole->givePermissionTo([]); // O asigna permisos minimos si los hay
        Log::info("Permisos asignados al rol 'basic user'.");


        Log::info('Asignacion de permisos a roles completada.');


        // --- 4. Asignar Roles a Usuarios ---
        Log::info('Asignando roles a usuarios...');

        // Define el email del empleado y usuario de ejemplo, y la contraseña
        $adminEmployeeEmail = 'super.admin.empleado@silcast.com';
        $adminUserEmail = 'admin@silcast.com';
        $adminUserPassword = 'password'; // ¡CAMBIALA!

        // --- Buscar o Crear Empleado (si el usuario requiere uno) ---
        $existingEmployee = Empleado::where('correo_electronico', $adminEmployeeEmail)->first();

        if (!$existingEmployee) {
             Log::info('Empleado de ejemplo no encontrado por email. Intentando crear uno...');
             try {
                 $existingEmployee = Empleado::create([
                     'numero_empleado' => '0001',
                     'nombre' => 'Super',
                     'apellido_paterno' => 'Admin',
                     'apellido_materno' => 'User',
                     'fecha_nacimiento' => '1990-01-01',
                     'correo_electronico' => $adminEmployeeEmail,
                     'telefono' => '5551234567',
                     'fecha_ingreso' => '2020-01-01',
                     'calle' => 'Calle Falsa 123',
                     'colonia' => 'Centro',
                     'cp' => '00000',
                     'municipio' => 'Ciudad Ejemplo',
                     'puesto' => 'Gerente',
                     'area' => 'Direccion',
                     'turno' => 'Matutino',
                     'estado_civil' => 'Casado',
                     // ¡Asegurate de añadir/ajustar AQUI todos los campos OBLIGATORIOS de tu tabla empleados!
                 ]);
                 Log::info('Empleado de ejemplo creado exitosamente.', ['empleado_id' => $existingEmployee->id, 'email' => $adminEmployeeEmail]);
             } catch (\Exception $e) {
                 Log::error('ERROR al crear empleado de ejemplo: ' . $e->getMessage(), ['exception' => $e, 'email_empleado' => $adminEmployeeEmail]);
                 $existingEmployee = null;
             }
        } else {
             Log::info('Empleado de ejemplo encontrado por email.', ['empleado_id' => $existingEmployee->id, 'email' => $adminEmployeeEmail]);
        }


        // --- Buscar o Crear Usuario ---
        $adminUser = Usuario::where('email', $adminUserEmail)->first();

        if (!$adminUser) {
            Log::info('Usuario super-admin no encontrado por email. Intentando crear uno...');
             if ($existingEmployee) {
                 try {
                     $adminUser = Usuario::create([
                         'empleado_id' => $existingEmployee->id,
                         'email' => $adminUserEmail,
                         'password' => Hash::make($adminUserPassword),
                         // ¡Asegurate de añadir/ajustar AQUI todos los campos OBLIGATORIOS de tu tabla usuarios!
                     ]);
                     Log::info('Usuario super-admin de ejemplo creado exitosamente.', ['usuario_id' => $adminUser->id, 'email' => $adminUserEmail, 'empleado_id' => $existingEmployee->id]);
                 } catch (\Exception $e) {
                      Log::error('ERROR al crear usuario super-admin de ejemplo: ' . $e->getMessage(), ['exception' => $e, 'email_usuario' => $adminUserEmail, 'empleado_id' => $existingEmployee->id ?? 'N/A']);
                      $adminUser = null;
                 }
             } else {
                  Log::warning('No se pudo crear el usuario super-admin porque no se encontro/creo un empleado valido.');
             }

        } else {
             Log::info('Usuario super-admin de ejemplo encontrado por email.', ['usuario_id' => $adminUser->id, 'email' => $adminUserEmail]);
        }


        // --- Asignar Rol 'super-admin' al Usuario ---
        if ($adminUser) {
            Log::info('Intentando asignar rol super-admin al usuario...');
            try { // <-- A\u00d1ADIDO try-catch alrededor de la asignacion
                // Opcional: Remueve roles existentes si quieres que solo tenga 'super-admin'
                // $adminUser->syncRoles([]);
                // Log::info("Roles existentes removidos para usuario ID: " . $adminUser->id);

                // Busca la instancia del rol 'super-admin'
                $superAdminRole = Role::findByName('super-admin');

                if ($superAdminRole) {
                     if (!$adminUser->hasRole('super-admin')) {
                          // AQUI OCURRE LA ASIGNACION EN LA BASE DE DATOS
                          $adminUser->assignRole($superAdminRole);
                          Log::info("Rol 'super-admin' asignado al usuario exitosamente.", ['usuario_id' => $adminUser->id, 'role_id' => $superAdminRole->id]);
                     } else {
                          Log::info("El usuario ya tiene el rol 'super-admin'. No se necesita re-asignar.", ['usuario_id' => $adminUser->id]);
                     }
                } else {
                     Log::error("ERROR: Rol 'super-admin' no encontrado por nombre. Asegurate de que se creo correctamente arriba.");
                }
            } catch (\Exception $e) { // <-- CATCH para errores durante assignRole()
                Log::error('ERROR CRITICO al asignar rol super-admin al usuario: ' . $e->getMessage(), ['exception' => $e, 'usuario_id' => $adminUser->id ?? 'N/A']);
            }

        } else {
             Log::warning("No se pudo asignar el rol 'super-admin' porque el usuario admin no es válido (null). La busqueda/creacion del usuario fallo.");
        }


        // --- Opcional: Crear otros usuarios de ejemplo y asignarles roles ---
        /* (Mantener o ajustar segun necesites, incluyendo try-catch similar) */


        Log::info('Asignacion de roles a usuarios completada.');
        Log::info('RolesAndPermissionsSeeder finalizado.');
    }
}