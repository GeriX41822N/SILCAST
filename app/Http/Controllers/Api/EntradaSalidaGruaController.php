<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntradasSalidasGrua; // Importa el modelo de Entradas/Salidas
use App\Models\Grua; // Importa el modelo Grua (para relaciones o validaciones)
use App\Models\Empleado; // Importa el modelo Empleado (para relaciones o validaciones)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Para logging
use App\Http\Requests\EntradaSalidaGruaRequest; // Importa el Form Request para validación y autorización
use Illuminate\Support\Facades\DB; // Para usar transacciones (opcional, pero recomendado para store/update)
use Illuminate\Support\Carbon; // Importa Carbon para manejar fechas


class EntradaSalidaGruaController extends Controller
{
    // Constructor para aplicar middleware de permisos (opcional pero organizado)
    // public function __construct()
    // {
    //     // Requiere permiso para ver lista y detalles de movimientos
    //     $this->middleware('permission:view movements', ['only' => ['index', 'show', 'filterByDate', 'getMovimientosByGrua']]);
    //     // Requiere permiso para crear movimientos
    //     $this->middleware('permission:create movements', ['only' => ['store']]);
    //     // Requiere permiso para actualizar movimientos
    //     $this->middleware('permission:edit movements', ['only' => ['update']]);
    //     // Requiere permiso para eliminar movimientos
    //     $this->middleware('permission:delete movements', ['only' => ['destroy']]);
    // }


    /**
     * Muestra una lista de registros de entrada/salida de grúas.
     * Requiere permiso 'view movements'.
     */
    public function index()
    {
        // Verificar permiso usando Spatie
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de acceso no autorizado a lista de movimientos de grúas.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver la lista de movimientos de grúas.'], 403);
        }

        try {
            // Obtener todos los movimientos.
            // Es importante cargar las relaciones con Grua y Empleado (Operador)
            // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado' para la relación con Empleado
            $movimientos = EntradasSalidasGrua::with(['grua', 'operador'])
                                             ->orderBy('fecha_hora_entrada', 'desc') // Ordenar por fecha/hora de entrada
                                             ->get();

            Log::info('Lista de movimientos de grúas cargada exitosamente.', ['user_id' => Auth::id(), 'count' => $movimientos->count()]);
            return response()->json($movimientos);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista de movimientos de grúas: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar movimientos de grúas.'], 500);
        }
    }

    /**
     * Almacena un nuevo registro de entrada o salida de grúa.
     * Requiere permiso 'create movements'.
     * @param EntradaSalidaGruaRequest $request // Usa el Form Request para validación
     */
    public function store(EntradaSalidaGruaRequest $request)
    {
        // La autorización ya se verifica en EntradaSalidaGruaRequest
        // if (!Auth::user()->can('create movements')) { ... }

        DB::beginTransaction(); // Iniciar transacción

        try {
            // Crear el registro de movimiento con los datos validados
            // El Form Request ya asegura que los datos son válidos y autorizados
            $movimiento = EntradasSalidasGrua::create($request->validated());

            // Opcional: Actualizar el estado de la grúa si es necesario (ej. 'disponible', 'en servicio')
            // Esto dependerá de la lógica de tu aplicación y si la tabla 'gruas' tiene un campo de estado
            // if ($movimiento->tipo_movimiento === 'salida') {
            //     // Asegúrate de cargar la relación grua si no la tienes precargada
            //     // $movimiento->load('grua');
            //     // if ($movimiento->grua) {
            //     //     $movimiento->grua->update(['estado' => 'en servicio']);
            //     // }
            // } elseif ($movimiento->tipo_movimiento === 'entrada') {
            //     // $movimiento->load('grua');
            //     // if ($movimiento->grua) {
            //     //     $movimiento->grua->update(['estado' => 'disponible']);
            //     // }
            // }


            DB::commit(); // Confirmar transacción

            Log::info('Registro de movimiento de grúa creado exitosamente.', ['movimiento_id' => $movimiento->id, 'user_id' => Auth::id()]);
            // Cargar relaciones para la respuesta
            // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado'
            $movimiento->load(['grua', 'operador']);
            return response()->json($movimiento, 201); // Código 201 Created

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción
            Log::error('Error al crear registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al crear registro de movimiento de grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un registro de entrada/salida de grúa específico.
     * Requiere permiso 'view movements'.
     * @param int $id
     */
    public function show(int $id)
    {
          // Verificar permiso usando Spatie
        if (!Auth::user()->can('view movements')) {
              Log::warning('Intento de ver movimiento de grúa no autorizado.', ['user_id' => Auth::id(), 'movimiento_id' => $id]);
             return response()->json(['message' => 'No tienes permiso para ver este registro de movimiento de grúa.'], 403);
         }

        try {
            // Encontrar el movimiento por su ID y cargar relaciones
            // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado'
            $movimiento = EntradasSalidasGrua::with(['grua', 'operador'])->findOrFail($id);

            Log::info('Registro de movimiento de grúa encontrado.', ['movimiento_id' => $movimiento->id, 'user_id' => Auth::id()]);
            return response()->json($movimiento);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              Log::warning('Registro de movimiento de grúa no encontrado.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Registro de movimiento de grúa no encontrado.'], 404);
        } catch (\Exception $e) {
              Log::error('Error al mostrar registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'movimiento_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al mostrar registro de movimiento de grúas.'], 500);
        }
    }

    /**
     * Actualiza un registro de entrada/salida de grúa específico.
     * Requiere permiso 'edit movements'.
     * @param EntradaSalidaGruaRequest $request // Usa el Form Request para validación
     * @param int $id
     */
    public function update(EntradaSalidaGruaRequest $request, int $id)
    {
        // La autorización ya se verifica en EntradaSalidaGruaRequest
        // if (!Auth::user()->can('edit movements')) { ... }

        DB::beginTransaction(); // Iniciar transacción

        try {
            // Encontrar el registro de movimiento
            $movimiento = EntradasSalidasGrua::findOrFail($id);

            // Opcional: Si cambias el tipo de movimiento o la grúa, podrías necesitar actualizar el estado de la grúa anterior/nueva.
            // Esto se vuelve complejo y podría requerir lógica adicional.
            // Considera si realmente necesitas actualizar registros de movimiento o si es mejor crear nuevos.


            // Actualizar el registro de movimiento con los datos validados
            $movimiento->update($request->validated());

            DB::commit(); // Confirmar transacción

            Log::info('Registro de movimiento de grúa actualizado exitosamente.', ['movimiento_id' => $movimiento->id, 'user_id' => Auth::id()]);
              // Cargar relaciones para la respuesta
             // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado'
             $movimiento->load(['grua', 'operador']);
            return response()->json($movimiento);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              DB::rollBack(); // Revertir transacción
              Log::warning('Registro de movimiento de grúa no encontrado para actualizar.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Registro de movimiento de grúa no encontrado.'], 404);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción
            Log::error('Error al actualizar registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'movimiento_id' => $id, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al actualizar registro de movimiento de grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un registro de entrada/salida de grúa específico.
     * Requiere permiso 'delete movements'.
     * @param int $id
     */
    public function destroy(int $id)
    {
        // Verificar permiso usando Spatie
        if (!Auth::user()->can('delete movements')) {
              Log::warning('Intento de eliminar movimiento de grúa no autorizado.', ['user_id' => Auth::id(), 'movimiento_id' => $id]);
             return response()->json(['message' => 'No tienes permiso para eliminar registros de movimientos de grúas.'], 403);
         }

        try {
            // Encontrar el registro de movimiento
            $movimiento = EntradasSalidasGrua::findOrFail($id);

            // Opcional: Si eliminas un movimiento, podrías necesitar revertir el estado de la grúa.
            // Esto también puede ser complejo. Considera si la eliminación es la mejor opción
            // o si un estado 'cancelado' es preferible.


            // Eliminar el registro de movimiento
            $movimiento->delete();

            Log::info('Registro de movimiento de grúa eliminado exitosamente.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);

            // Devolver una respuesta de éxito sin contenido
            return response()->json(null, 204); // Código 204 No Content

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              Log::warning('Registro de movimiento de grúa no encontrado para eliminar.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Registro de movimiento de grúa no encontrado.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'movimiento_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar registro de movimiento de grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene registros de entrada/salida de grúas filtrados por rango de fechas y/o grúa.
     * Requiere permiso 'view movements'.
     * @param Request $request
     */
    public function filterByDate(Request $request) // Nombre del método ajustado para reflejar que también puede filtrar por grúa
    {
        // Verificar permiso usando Spatie
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de acceso no autorizado a filtro de movimientos de grúas.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver la lista de movimientos de grúas.'], 403);
        }

        try {
            // Obtener los parámetros del request
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $gruaId = $request->query('grua_id'); // Obtener ID de grúa del request

            // Validar que al menos un filtro fue proporcionado (fecha o grúa)
            if (empty($startDate) && empty($endDate) && empty($gruaId)) {
                 // Si no se proporcionan filtros, devolver todos los movimientos (comportamiento por defecto)
                 // O podrías devolver un error 422 si al menos un filtro es obligatorio
                 Log::info('Filtro de movimientos llamado sin parámetros, devolviendo todos los movimientos.', ['user_id' => Auth::id()]);
                 return $this->index(); // Llama al método index para obtener todos los movimientos
             }

            // Iniciar la consulta con las relaciones necesarias
            // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado'
            $query = EntradasSalidasGrua::with(['grua', 'operador']);

            // Aplicar filtro por fecha de inicio si se proporciona
            if (!empty($startDate)) {
                // Usar Carbon para asegurar formato y añadir la hora de inicio del día
                $start = Carbon::parse($startDate)->startOfDay();
                $query->where('fecha_hora_entrada', '>=', $start); // Filtrar por fecha_hora_entrada
            }

            // Aplicar filtro por fecha de fin si se proporciona
            if (!empty($endDate)) {
                // Usar Carbon para asegurar formato y añadir la hora de fin del día
                 $end = Carbon::parse($endDate)->endOfDay();
                $query->where('fecha_hora_entrada', '<=', $end); // Filtrar por fecha_hora_entrada
            }

            // Aplicar filtro por grúa si se proporciona
            if (!empty($gruaId)) {
                 // Opcional: Verificar si la grúa existe antes de filtrar
                 // try { Grua::findOrFail($gruaId); } catch (\Exception $e) { return response()->json(['message' => 'La Grúa seleccionada no existe.'], 404); }
                 $query->where('grua_id', $gruaId);
            }


            // Ordenar los resultados
            $movimientos = $query->orderBy('fecha_hora_entrada', 'desc')->get(); // Ordenar por fecha_hora_entrada

            Log::info('Lista de movimientos de grúas filtrada cargada exitosamente.', [
                'user_id' => Auth::id(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'grua_id' => $gruaId,
                'count' => $movimientos->count()
            ]);

            return response()->json($movimientos);

        } catch (\Exception $e) {
            Log::error('Error al filtrar movimientos de grúas: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al filtrar movimientos de grúas.', 'error' => $e->getMessage()], 500);
        }
    }

      /**
      * Obtiene los registros de movimientos de una grúa específica.
      * Requiere permiso 'view movements'.
      * @param int $gruaId
      *
      * NOTA: Este método podría ser redundante si filterByDate maneja el filtro por grua_id.
      * Si solo necesitas filtrar por grúa, puedes usar este método. Si necesitas combinar
      * filtro por fecha Y grúa, filterByDate es más adecuado.
      * Mantenemos este método por ahora si lo necesitas separado.
      */
     public function getMovimientosByGrua(int $gruaId)
     {
           // Verificar permiso
         if (!Auth::user()->can('view movements')) {
               Log::warning('Intento de ver movimientos por grúa no autorizado.', ['user_id' => Auth::id(), 'grua_id' => $gruaId]);
              return response()->json(['message' => 'No tienes permiso para ver estos movimientos.'], 403);
          }

         try {
               // Opcional: Encontrar la grúa para asegurar que existe.
               // Si la grúa no existe, findOrFail lanzará una excepción ModelNotFoundException.
               $grua = Grua::findOrFail($gruaId);
               Log::info('Grúa encontrada para filtrar movimientos.', ['grua_id' => $gruaId, 'user_id' => Auth::id()]);


             $movimientos = EntradasSalidasGrua::where('grua_id', $gruaId)
                                              // ¡CORREGIDO! Usamos 'operador' en lugar de 'empleado'
                                             ->with(['grua', 'operador'])
                                              ->orderBy('fecha_hora_entrada', 'desc') // Ordenar por fecha/hora de entrada
                                              ->get();

               Log::info('Movimientos para grúa cargados exitosamente.', ['grua_id' => $gruaId, 'user_id' => Auth::id(), 'count' => $movimientos->count()]);
             return response()->json($movimientos);

          } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
               Log::warning('Grúa no encontrada al intentar obtener movimientos por grúa.', ['grua_id' => $gruaId, 'user_id' => Auth::id()]);
               return response()->json(['message' => 'Grúa no encontrada.'], 404);
          } catch (\Exception $e) {
               Log::error('Error al cargar movimientos por grúa: ' . $e->getMessage(), ['exception' => $e, 'grua_id' => $gruaId, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar movimientos por grúa.'], 500);
        }
    }

    // Puedes añadir métodos para filtrar por empleado, etc.

}
