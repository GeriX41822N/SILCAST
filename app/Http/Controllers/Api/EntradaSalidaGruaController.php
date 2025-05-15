<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntradasSalidasGrua;
use App\Models\Grua;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EntradaSalidaGruaRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EntradaSalidaGruaController extends Controller
{
    /**
     * Muestra una lista de todos los movimientos de grúas.
     * Requiere el permiso 'view movements'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de acceso no autorizado a lista de movimientos de grúas.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver la lista de movimientos de grúas.'], 403);
        }

        try {
            $movimientos = EntradasSalidasGrua::with(['grua', 'operador'])
                                                ->orderBy('fecha_hora_entrada', 'desc')
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
     *
     * @param EntradaSalidaGruaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EntradaSalidaGruaRequest $request)
    {
        DB::beginTransaction();

        try {
            $movimiento = EntradasSalidasGrua::create($request->validated());
            DB::commit();

            Log::info('Registro de movimiento de grúa creado exitosamente.', ['movimiento_id' => $movimiento->id, 'user_id' => Auth::id()]);
            $movimiento->load(['grua', 'operador']);
            return response()->json($movimiento, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al crear registro de movimiento de grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un registro de entrada/salida de grúa específico.
     * Requiere permiso 'view movements'.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de ver movimiento de grúa no autorizado.', ['user_id' => Auth::id(), 'movimiento_id' => $id]);
            return response()->json(['message' => 'No tienes permiso para ver este registro de movimiento de grúa.'], 403);
        }

        try {
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
     *
     * @param EntradaSalidaGruaRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EntradaSalidaGruaRequest $request, int $id)
    {
        DB::beginTransaction();

        try {
            $movimiento = EntradasSalidasGrua::findOrFail($id);
            $movimiento->update($request->validated());
            DB::commit();

            Log::info('Registro de movimiento de grúa actualizado exitosamente.', ['movimiento_id' => $movimiento->id, 'user_id' => Auth::id()]);
            $movimiento->load(['grua', 'operador']);
            return response()->json($movimiento);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Registro de movimiento de grúa no encontrado para actualizar.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Registro de movimiento de grúa no encontrado.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar registro de movimiento de grúa: ' . $e->getMessage(), ['exception' => $e, 'movimiento_id' => $id, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al actualizar registro de movimiento de grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un registro de entrada/salida de grúa específico.
     * Requiere permiso 'delete movements'.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        if (!Auth::user()->can('delete movements')) {
            Log::warning('Intento de eliminar movimiento de grúa no autorizado.', ['user_id' => Auth::id(), 'movimiento_id' => $id]);
            return response()->json(['message' => 'No tienes permiso para eliminar registros de movimientos de grúas.'], 403);
        }

        try {
            $movimiento = EntradasSalidasGrua::findOrFail($id);
            $movimiento->delete();
            Log::info('Registro de movimiento de grúa eliminado exitosamente.', ['movimiento_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(null, 204);

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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByDate(Request $request)
    {
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de acceso no autorizado a filtro de movimientos de grúas.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver la lista de movimientos de grúas.'], 403);
        }

        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $gruaId = $request->query('grua_id');

            if (empty($startDate) && empty($endDate) && empty($gruaId)) {
                Log::info('Filtro de movimientos llamado sin parámetros, devolviendo todos los movimientos.', ['user_id' => Auth::id()]);
                return $this->index();
            }

            $query = EntradasSalidasGrua::with(['grua', 'operador']);

            if (!empty($startDate)) {
                $start = Carbon::parse($startDate)->startOfDay();
                $query->where('fecha_hora_entrada', '>=', $start);
            }

            if (!empty($endDate)) {
                $end = Carbon::parse($endDate)->endOfDay();
                $query->where('fecha_hora_entrada', '<=', $end);
            }

            if (!empty($gruaId)) {
                $query->where('grua_id', $gruaId);
            }

            $movimientos = $query->orderBy('fecha_hora_entrada', 'desc')->get();

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
     *
     * @param int $gruaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMovimientosByGrua(int $gruaId)
    {
        if (!Auth::user()->can('view movements')) {
            Log::warning('Intento de ver movimientos por grúa no autorizado.', ['user_id' => Auth::id(), 'grua_id' => $gruaId]);
            return response()->json(['message' => 'No tienes permiso para ver estos movimientos.'], 403);
        }

        try {
            $grua = Grua::findOrFail($gruaId);
            Log::info('Grúa encontrada para filtrar movimientos.', ['grua_id' => $gruaId, 'user_id' => Auth::id()]);

            $movimientos = EntradasSalidasGrua::where('grua_id', $gruaId)
                                                ->with(['grua', 'operador'])
                                                ->orderBy('fecha_hora_entrada', 'desc')
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
}