<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\GruaController; 
use App\Http\Controllers\Api\EntradaSalidaGruaController; 
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\UserController;

// --- Rutas de autenticacion publica (no requieren token) ---
// Estas rutas deben estar fuera del middleware 'auth:sanctum'
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']); // <-- Esta es la ruta de login


// --- Rutas protegidas por Sanctum ---
Route::middleware('auth:sanctum')->group(function () {
    // Ruta para cerrar sesion
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Ruta para obtener los datos del usuario autenticado 
    Route::get('/user', function (Request $request) {
        // Cargar roles y permisos del usuario autenticado
        $user = $request->user();
        $user->load('roles.permissions');
        return $user->makeHidden(['password', 'remember_token']);
    });


    Route::apiResource('empleados', EmpleadoController::class);

    Route::get('employees-list', [EmpleadoController::class, 'getEmpleadosListSimple']); // <--- Esta es la l\u00EDnea a\u00F1adida/corregida


    Route::get('roles', [EmpleadoController::class, 'getRoles']);


    Route::apiResource('gruas', GruaController::class);

    Route::get('gruas-list-simple', [GruaController::class, 'getGruasListSimple']);


   Route::apiResource('movimientos-grua', EntradaSalidaGruaController::class);

   Route::get('movimientos-grua/filter-by-date', [EntradaSalidaGruaController::class, 'filterByDate']);

   Route::get('movimientos-grua/by-grua/{gruaId}', [EntradaSalidaGruaController::class, 'getMovimientosByGrua']);


    Route::apiResource('proveedores', ProveedorController::class);
    
      // Rutas de Usuarios (para la gestion de usuarios)
      Route::apiResource('users', UserController::class); // Define rutas CRUD estandar para UserController


      // Ruta para obtener roles - ¡CORREGIDA para apuntar al UserController!
      Route::get('roles', [UserController::class, 'getRoles']); // <-- Ahora busca el metodo getRoles en UserController
  
  

});
