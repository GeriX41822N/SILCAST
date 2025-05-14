<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\GruaController;
use App\Http\Controllers\Api\EntradaSalidaGruaController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Este archivo define las rutas de tu API RESTful. Las rutas aquí definidas
| serán prefijadas automáticamente con '/api' y protegidas por el middleware
| 'api' por defecto (definido en App\Providers\RouteServiceProvider).
|
*/

// --- Rutas de Autenticación Públicas (No requieren token Sanctum) ---
// Estas rutas son accesibles sin necesidad de autenticación previa.
Route::group(['prefix' => 'auth'], function () {
    // Ruta para registrar un nuevo usuario
    Route::post('/register', [AuthController::class, 'register']);
    // Ruta para iniciar sesión y obtener un token Sanctum
    Route::post('/login', [AuthController::class, 'login']);
});


// --- Rutas Protegidas (Requieren autenticación con token Sanctum) ---
// Todas las rutas dentro de este grupo requieren un token Sanctum válido
// en la cabecera 'Authorization: Bearer <token>'.
Route::middleware(['auth:sanctum', 'cors'])->group(function () { // ¡Middleware 'cors' añadido aquí!

    // Gestión de Usuarios (Users)
    // ** IMPORTANTE: Definir rutas específicas ANTES de apiResource para este prefijo **

    // Ruta específica para obtener la lista de roles del sistema.
    // GET /api/users/roles
    // DEBE IR ANTES de la definición de apiResource('users', ...)
    Route::get('users/roles', [UserController::class, 'getRoles']);

    // Ruta para obtener los datos del usuario autenticado (ruta '/user' sin prefijo de recurso)
    // GET /api/user
    // También es una ruta específica, aunque el conflicto aquí es menor, por claridad se puede dejar aquí.
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('roles.permissions');
        return $user->makeHidden(['password', 'remember_token']);
    });

    // Define las rutas estándar de recursos (CRUD) para la gestión de usuarios.
    // GET /api/users, POST /api/users, GET /api/users/{user}, PUT/PATCH /api/users/{user}, DELETE /api/users/{user}
    // Esta definición DEBE IR DESPUÉS de las rutas específicas con el prefijo 'users'.
    Route::apiResource('users', UserController::class);


    // Ruta para cerrar sesión
    // Invalida el token Sanctum actual del usuario autenticado.
    // POST /api/auth/logout
    // Colocada después de apiResource('users') ya que usa un prefijo de grupo 'auth', no conflicto directo.
    Route::post('/auth/logout', [AuthController::class, 'logout']);


    // Gestión de Empleados (Employees)
    // ** IMPORTANTE: Definir rutas específicas ANTES de apiResource para este prefijo **

    // Ruta específica para obtener una lista simplificada de empleados (para dropdowns).
    // GET /api/employees-list
    // DEBE IR ANTES de la definición de apiResource('empleados', ...)
    Route::get('employees-list', [EmpleadoController::class, 'getEmpleadosListSimple']);

    // Define las rutas estándar de recursos (CRUD) para la gestión completa de empleados.
    // GET /api/empleados, POST /api/empleados, GET /api/empleados/{empleado}, PUT/PATCH /api/empleados/{empleado}, DELETE /api/empleados/{empleado}
    // Esta definición DEBE IR DESPUÉS de rutas específicas como 'employees-list'.
    Route::apiResource('empleados', EmpleadoController::class);


    // Gestión de Grúas (Cranes)
    // ** IMPORTANTE: Definir rutas específicas ANTES de apiResource para este prefijo **

    // Ruta específica para obtener una lista simplificada de grúas (para dropdowns).
    // GET /api/gruas-list-simple
    // DEBE IR ANTES de la definición de apiResource('gruas', ...)
    Route::get('gruas-list-simple', [GruaController::class, 'getGruasListSimple']);

    // Define las rutas estándar de recursos (CRUD) para la gestión completa de grúas.
    // GET /api/gruas, POST /api/gruas, GET /api/gruas/{grua}, PUT/PATCH /api/gruas/{grua}, DELETE /api/gruas/{grua}
    // Esta definición DEBE IR DESPUÉS de rutas específicas como 'gruas-list-simple'.
    Route::apiResource('gruas', GruaController::class);


    // Gestión de Movimientos de Grúas (Crane Movements)
    // ** IMPORTANTE: Definir rutas específicas ANTES de apiResource para este prefijo **

    // Ruta específica para filtrar movimientos por rango de fechas y/o grúa.
    // GET /api/movimientos-grua/filter-by-date?start_date=...&end_date=...&grua_id=...
    // DEBE IR ANTES de la definición de apiResource para 'movimientos-grua'.
    Route::get('movimientos-grua/filter-by-date', [EntradaSalidaGruaController::class, 'filterByDate']);

    // Ruta específica para obtener movimientos filtrados por una grúa específica.
    // GET /api/movimientos-grua/by-grua/{gruaId}
    // DEBE IR ANTES de la definición de apiResource para 'movimientos-grua'.
    Route::get('movimientos-grua/by-grua/{gruaId}', [EntradaSalidaGruaController::class, 'getMovimientosByGrua']);

    // Define las rutas estándar de recursos (CRUD) para la gestión completa de movimientos de grúas.
    // GET /api/movimientos-grua, POST /api/movimientos-grua, GET /api/movimientos-grua/{movimientos_grua}, PUT/PATCH /api/movimientos-grua/{movimientos_grua}, DELETE /api/movimientos-grua/{movimientos_grua}
    // Esta definición DEBE IR DESPUÉS de las rutas específicas con el mismo prefijo.
    Route::apiResource('movimientos-grua', EntradaSalidaGruaController::class);


    // Gestión de Proveedores (Suppliers)
    // Define las rutas estándar de recursos (CRUD) para la gestión completa de proveedores.
    // GET /api/proveedores, POST /api/proveedores, GET /api/proveedores/{proveedor}, PUT/PATCH /api/proveedores/{proveedor}, DELETE /api/proveedores/{proveedor}
    // Como no hay rutas específicas con prefijo 'proveedores', apiResource puede ir aquí.
    Route::apiResource('proveedores', ProveedorController::class);


    // --- Otras rutas protegidas adicionales pueden ir aquí ---

});

// --- Rutas públicas adicionales pueden ir aquí afuera del grupo middleware ---