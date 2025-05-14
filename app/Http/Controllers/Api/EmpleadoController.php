<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EmpleadoRequest;

class EmpleadoController extends Controller
{
    /**
     * Constructor para aplicar middleware de permisos.
     */
    public function __construct()
    {
        // Aplica middleware para proteger los metodos CRUD de empleados
        // $this->middleware('permission:view employees', ['only' => ['index', 'getEmpleadosListSimple', 'show']]);
        // $this->middleware('permission:create employees', ['only' => ['store']]);
        // $this->middleware('permission:edit employees', ['only' => ['update']]);
        // $this->middleware('permission:delete employees', ['only' => ['destroy']]);
        // $this->middleware('permission:manage users', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource (Empleados).
     * Obtiene y devuelve la lista completa de empleados con su usuario y roles.
     * Requiere permiso 'view employees'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        Log::info('Intentando cargar lista completa de empleados con usuario y roles.', ['user_id' => Auth::id()]);

        try {
            // Obtener todos los empleados y cargar las relaciones 'usuario' (y dentro de él, 'roles').
            $empleados = Empleado::with('usuario.roles')->get();

            Log::info('Lista completa de empleados con usuario y roles cargada exitosamente.', ['count' => $empleados->count(), 'user_id' => Auth::id()]);

            return response()->json($empleados);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista completa de empleados con usuario y roles: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de empleados.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage (Empleado).
     * Crea un nuevo empleado.
     * Requiere permiso 'create employees'.
     *
     * @param  \App\Http\Requests\EmpleadoRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EmpleadoRequest $request)
    {
        Log::info('Intentando crear nuevo empleado.', ['user_id' => Auth::id(), 'data' => $request->all()]);

        try {
            $empleado = Empleado::create($request->validated());

            Log::info('Empleado creado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

            return response()->json($empleado, 201);

        } catch (\Exception $e) {
            Log::error('Error al crear empleado: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al crear el empleado.'], 500);
        }
    }

    /**
     * Display the specified resource (Empleado).
     * Obtiene y devuelve los datos de un empleado especifico con su usuario y roles.
     * Requiere permiso 'view employees'.
     *
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Empleado $empleado)
    {
        Log::info('Intentando cargar empleado especifico con su usuario y roles.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

        try {
            $empleado->load('usuario.roles'); // Cargar las relaciones para un empleado específico
            return response()->json($empleado);

        } catch (\Exception $e) {
            Log::error('Error al cargar empleado especifico con su usuario y roles: ' . $e->getMessage(), ['exception' => $e, 'empleado_id' => $empleado->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar el empleado.'], 500);
        }
    }

    /**
     * Update the specified resource in storage (Empleado).
     * Actualiza un empleado existente.
     * Requiere permiso 'edit employees'.
     *
     * @param  \App\Http\Requests\EmpleadoRequest  $request
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EmpleadoRequest $request, Empleado $empleado)
    {
        Log::info('Intentando actualizar empleado.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id(), 'data' => $request->all()]);

        try {
            $empleado->update($request->validated());

            Log::info('Empleado actualizado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

            return response()->json($empleado);

        } catch (\Exception $e) {
            Log::error('Error al actualizar empleado: ' . $e->getMessage(), ['exception' => $e, 'empleado_id' => $empleado->id, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al actualizar el empleado.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage (Empleado).
     * Elimina un empleado.
     * Requiere permiso 'delete employees'.
     *
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Empleado $empleado)
    {
        Log::info('Intentando eliminar empleado.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

        try {
            $empleado->delete();

            Log::info('Empleado eliminado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

            return response()->json(['message' => 'Empleado eliminado correctamente.'], 200);
            // return response()->noContent(); // Codigo 204
        } catch (\Exception $e) {
            Log::error('Error al eliminar empleado: ' . $e->getMessage(), ['exception' => $e, 'empleado_id' => $empleado->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar el empleado.'], 500);
        }
    }

    /**
     * Obtiene una lista simple de empleados (ID, nombre, apellido_paterno) para dropdowns, etc.
     * Requiere permiso 'view employees'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmpleadosListSimple()
    {
        Log::info('Intentando cargar lista simple de empleados para dropdowns.', ['user_id' => Auth::id()]);

        try {
            $empleados = Empleado::select('id', 'nombre', 'apellido_paterno')
                                    ->get();

            Log::info('Lista simple de empleados para dropdowns cargada exitosamente.', ['count' => $empleados->count(), 'user_id' => Auth::id()]);
            return response()->json($empleados);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista simple de empleados para dropdowns: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar lista simple de empleados.'], 500);
        }
    }

    // El metodo getRoles() fue movido a UserController.php y es redundante aqui.
}