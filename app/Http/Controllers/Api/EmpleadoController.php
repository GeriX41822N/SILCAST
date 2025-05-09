<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado; // Asegúrate de que tu modelo Empleado está en App\Models
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EmpleadoRequest; // Asegúrate de que este Request existe y tiene las reglas de validacion
// use Illuminate\Validation\ValidationException; // Si quieres manejar errores de validacion explicitamente

class EmpleadoController extends Controller
{
    /**
     * Constructor para aplicar middleware de permisos.
     * Asegura que solo usuarios con los permisos correctos puedan acceder a los metodos.
     */
    public function __construct()
    {
        // Aplica middleware para proteger los metodos CRUD de empleados
        // Asegura que solo usuarios con 'view employees' puedan listar y ver detalles
        $this->middleware('permission:view employees', ['only' => ['index', 'getEmpleadosListSimple', 'show']]);
        // Asegura que solo usuarios con 'create employees' puedan crear
        $this->middleware('permission:create employees', ['only' => ['store']]);
        // Asegura que solo usuarios con 'edit employees' puedan actualizar
        $this->middleware('permission:edit employees', ['only' => ['update']]);
        // Asegura que solo usuarios con 'delete employees' puedan eliminar
        $this->middleware('permission:delete employees', ['only' => ['destroy']]);
         // Opcional: Si 'manage users' implica gestion de empleados tambien, puedes añadirlo a algunos
         // $this->middleware('permission:manage users', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }


    /**
     * Display a listing of the resource (Empleados).
     * Obtiene y devuelve la lista completa de empleados.
     * Requiere permiso 'view employees'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // El permiso 'view employees' se verifica automaticamente por el middleware en el constructor.
        Log::info('Intentando cargar lista completa de empleados.', ['user_id' => Auth::id()]);

        try {
            // Obtener todos los empleados. Puedes eager load relaciones si las necesitas:
            // $empleados = Empleado::with(['usuario', 'supervisor'])->get();

            // Para listas largas, considera paginacion:
            // $empleados = Empleado::paginate(15); // Devuelve una estructura paginada

            // Por ahora, obtener todos:
            $empleados = Empleado::all();

            Log::info('Lista completa de empleados cargada exitosamente.', ['count' => $empleados->count(), 'user_id' => Auth::id()]);

            return response()->json($empleados);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista completa de empleados: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            // Devuelve una respuesta de error adecuada, sin exponer detalles internos al cliente
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de empleados.'], 500);
        }
    }


    /**
     * Store a newly created resource in storage (Empleado).
     * Crea un nuevo empleado.
     * Requiere permiso 'create employees'.
     *
     * @param  \App\Http\Requests\EmpleadoRequest  $request // Asume que usas un FormRequest para validacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EmpleadoRequest $request) // Si no usas FormRequest, cambia a Request $request
    {
         // El permiso 'create employees' se verifica automaticamente por el middleware.
         Log::info('Intentando crear nuevo empleado.', ['user_id' => Auth::id(), 'data' => $request->all()]);

         try {
             // Usa el metodo create() del modelo con los datos validados del FormRequest.
             // Esto funciona porque los campos en el array $request->validated() estan en el $fillable del modelo.
             $empleado = Empleado::create($request->validated());

             Log::info('Empleado creado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

             // Devuelve el empleado creado con un codigo de estado 201 Created
             return response()->json($empleado, 201);

         } catch (\Exception $e) {
             // Puedes anadir manejo especifico para ValidationException si no usas FormRequest y validas manualmente
             /*
             if ($e instanceof ValidationException) {
                 return response()->json(['errors' => $e->errors()], 422); // 422 Unprocessable Entity
             }
             */
             Log::error('Error al crear empleado: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
             return response()->json(['message' => 'Error interno del servidor al crear el empleado.'], 500);
         }
    }


    /**
     * Display the specified resource (Empleado).
     * Obtiene y devuelve los datos de un empleado especifico.
     * Requiere permiso 'view employees'.
     *
     * @param  \App\Models\Empleado  $empleado // Inyeccion de modelo por Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Empleado $empleado) // Asegurate que el nombre del parametro ({empleado}) en la ruta (api.php) coincida con la variable ($empleado)
    {
        // El permiso 'view employees' se verifica automaticamente por el middleware.
        Log::info('Intentando cargar empleado especifico.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

        try {
            // Gracias a Route Model Binding, $empleado ya es la instancia del modelo encontrada por el ID en la URL.
            // Puedes cargar relaciones si las necesitas para la vista detallada:
            // $empleado->load(['usuario', 'supervisor']);

            return response()->json($empleado);

        } catch (\Exception $e) {
             Log::error('Error al cargar empleado especifico: ' . $e->getMessage(), ['exception' => $e, 'empleado_id' => $empleado->id, 'user_id' => Auth::id()]);
             return response()->json(['message' => 'Error interno del servidor al cargar el empleado.'], 500);
        }
    }


    /**
     * Update the specified resource in storage (Empleado).
     * Actualiza un empleado existente.
     * Requiere permiso 'edit employees'.
     *
     * @param  \App\Http\Requests\EmpleadoRequest  $request // Asume que usas un FormRequest para validacion
     * @param  \App\Models\Empleado  $empleado // Inyeccion de modelo por Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EmpleadoRequest $request, Empleado $empleado) // Si no usas FormRequest, cambia a Request $request
    {
         // El permiso 'edit employees' se verifica automaticamente por el middleware.
         Log::info('Intentando actualizar empleado.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id(), 'data' => $request->all()]);

         try {
             // Actualiza la instancia del modelo con los datos validados.
             // Esto funciona porque los campos en el array $request->validated() estan en el $fillable del modelo.
             $empleado->update($request->validated());

             Log::info('Empleado actualizado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

             // Devuelve el empleado actualizado
             return response()->json($empleado);

         } catch (\Exception $e) {
             // Puedes anadir manejo especifico para ValidationException si no usas FormRequest y validas manualmente
             Log::error('Error al actualizar empleado: ' . $e->getMessage(), ['exception' => $e, 'empleado_id' => $empleado->id, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
             return response()->json(['message' => 'Error interno del servidor al actualizar el empleado.'], 500);
         }
    }


    /**
     * Remove the specified resource from storage (Empleado).
     * Elimina un empleado.
     * Requiere permiso 'delete employees'.
     *
     * @param  \App\Models\Empleado  $empleado // Inyeccion de modelo por Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Empleado $empleado) // Asegurate que el nombre del parametro ({empleado}) en la ruta (api.php) coincida con la variable ($empleado)
    {
         // El permiso 'delete employees' se verifica automaticamente por el middleware.
         Log::info('Intentando eliminar empleado.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

         // Opcional: Verifica si hay restricciones de clave foranea antes de eliminar
         // Por ejemplo, si un usuario o reporte depende de este empleado, podrias impedirlo.
         /*
         if ($empleado->usuario()->exists()) {
              return response()->json(['message' => 'No se puede eliminar el empleado porque tiene un usuario asociado.'], 409); // 409 Conflict
         }
         // ... otras verificaciones ...
         */


         try {
             $empleado->delete();

             Log::info('Empleado eliminado exitosamente.', ['empleado_id' => $empleado->id, 'user_id' => Auth::id()]);

             // Devuelve una respuesta de exito. 200 OK con mensaje o 204 No Content es comun para eliminacion exitosa.
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
        // El permiso 'view employees' se verifica automaticamente por el middleware.
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