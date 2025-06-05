<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\GruaController;
use App\Http\Controllers\Api\EntradaSalidaGruaController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ContactFormController; // Importa el controlador del formulario de contacto

/*
|--------------------------------------------------------------------------
| Rutas de API
|--------------------------------------------------------------------------
|
| Este archivo contiene la definición de todas las rutas de la API de la
| aplicación. Estas rutas son automáticamente prefijadas con '/api' y
| se agrupan por su naturaleza (públicas o protegidas por autenticación).
|
*/

// Grupo de rutas públicas, con middleware CORS aplicado para permitir peticiones desde el frontend.
Route::middleware(['cors'])->group(function () {

    // Rutas de Autenticación
    Route::prefix('auth')->group(function () {
        // Ruta para registrar un nuevo usuario en el sistema.
        Route::post('/register', [AuthController::class, 'register']);
        // Ruta para iniciar sesión y obtener un token de acceso (Sanctum).
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Ruta para el envío del formulario de contacto.
    // Esta ruta es accesible públicamente sin autenticación.
    Route::post('/enviar-correo', [ContactFormController::class, 'sendEmail']);

});


// Grupo de rutas protegidas, que requieren un token de autenticación (Sanctum)
// y también tienen el middleware CORS aplicado.
Route::middleware(['auth:sanctum', 'cors'])->group(function () {

    // Rutas de Gestión de Usuarios
    // Las rutas específicas deben ir antes de las rutas de recurso.
    Route::get('users/roles', [UserController::class, 'getRoles']);
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('roles.permissions');
        return $user->makeHidden(['password', 'remember_token']);
    });
    Route::apiResource('users', UserController::class);

    // Ruta para cerrar sesión del usuario autenticado.
    Route::post('/auth/logout', [AuthController::class, 'logout']);


    // Rutas de Gestión de Empleados
    // Las rutas específicas deben ir antes de las rutas de recurso.
    Route::get('employees-list', [EmpleadoController::class, 'getEmpleadosListSimple']);
    Route::apiResource('empleados', EmpleadoController::class);


    // Rutas de Gestión de Grúas
    // Las rutas específicas deben ir antes de las rutas de recurso.
    Route::get('gruas-list-simple', [GruaController::class, 'getGruasListSimple']);
    Route::apiResource('gruas', GruaController::class);


    // Rutas de Gestión de Movimientos de Grúas
    // Las rutas específicas deben ir antes de las rutas de recurso.
    Route::get('movimientos-grua/filter-by-date', [EntradaSalidaGruaController::class, 'filterByDate']);
    Route::get('movimientos-grua/by-grua/{gruaId}', [EntradaSalidaGruaController::class, 'getMovimientosByGrua']);
    Route::apiResource('movimientos-grua', EntradaSalidaGruaController::class);


    // Rutas de Gestión de Proveedores
    Route::apiResource('proveedores', ProveedorController::class);

});