<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grua; // Importa el modelo Grua
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Para logging
use App\Http\Requests\GruaRequest; // Importa el Form Request para validación y autorización

class GruaController extends Controller
{
    /**
     * Muestra una lista de grúas.
     * Requiere permiso 'view gruas'.
     */
    public function index()
    {
        // Verificar permiso usando Spatie
        if (!Auth::user()->can('view gruas')) {
            Log::warning('Intento de acceso no autorizado a lista de grúas.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver la lista de grúas.'], 403);
        }

        try {
            // Obtener todas las grúas. Puedes añadir eager loading si tienen relaciones.
            $gruas = Grua::all(); // O Grua::with(['relacion1', 'relacion2'])->get();

            Log::info('Lista de grúas (index) cargada exitosamente.', ['user_id' => Auth::id()]);
            return response()->json($gruas);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista de grúas (index): ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar grúas.'], 500);
        }
    }

    /**
     * Almacena una nueva grúa.
     * Requiere permiso 'create gruas'.
     * @param GruaRequest $request // Usa el Form Request para validación
     */
    public function store(GruaRequest $request)
    {
        // La autorización ya se verifica en GruaRequest
        // if (!Auth::user()->can('create gruas')) { ... }

        try {
            // Crear la grúa con los datos validados
            $grua = Grua::create($request->validated());

            Log::info('Grúa creada exitosamente.', ['grua_id' => $grua->id, 'user_id' => Auth::id()]);
            return response()->json($grua, 201); // Código 201 Created

        } catch (\Exception $e) {
            Log::error('Error al crear grúa: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al crear grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra una grúa específica.
     * Requiere permiso 'view gruas'.
     * @param int $id
     */
    public function show(int $id)
    {
          // Verificar permiso usando Spatie
        if (!Auth::user()->can('view gruas')) {
              Log::warning('Intento de ver grúa no autorizado.', ['user_id' => Auth::id(), 'grua_id' => $id]);
             return response()->json(['message' => 'No tienes permiso para ver esta grúa.'], 403);
          }

        try {
            // Encontrar la grúa por su ID. Puedes añadir eager loading si tiene relaciones.
            $grua = Grua::findOrFail($id); // O Grua::with(['relacion1'])->findOrFail($id);

            Log::info('Grúa encontrada.', ['grua_id' => $grua->id, 'user_id' => Auth::id()]);
            return response()->json($grua);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              Log::warning('Grúa no encontrada.', ['grua_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Grúa no encontrada.'], 404);
        } catch (\Exception $e) {
              Log::error('Error al mostrar grúa: ' . $e->getMessage(), ['exception' => $e, 'grua_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al mostrar grúa.'], 500);
        }
    }

    /**
     * Actualiza una grúa específica.
     * Requiere permiso 'edit gruas'.
     * @param GruaRequest $request // Usa el Form Request para validación
     * @param int $id
     */
    public function update(GruaRequest $request, int $id)
    {
        // La autorización ya se verifica en GruaRequest
        // if (!Auth::user()->can('edit gruas')) { ... }

        try {
            // Encontrar la grúa
            $grua = Grua::findOrFail($id);

            // Actualizar la grúa con los datos validados
            $grua->update($request->validated());

            Log::info('Grúa actualizada exitosamente.', ['grua_id' => $grua->id, 'user_id' => Auth::id()]);
            return response()->json($grua);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              Log::warning('Grúa no encontrada para actualizar.', ['grua_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Grúa no encontrada.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar grúa: ' . $e->getMessage(), ['exception' => $e, 'grua_id' => $id, 'user_id' => Auth::id(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Error interno del servidor al actualizar grúa.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una grúa específica.
     * Requiere permiso 'delete gruas'.
     * @param int $id
     */
    public function destroy(int $id)
    {
        // Verificar permiso usando Spatie
        if (!Auth::user()->can('delete gruas')) {
              Log::warning('Intento de eliminar grúa no autorizado.', ['user_id' => Auth::id(), 'grua_id' => $id]);
             return response()->json(['message' => 'No tienes permiso para eliminar grúas.'], 403);
          }

        try {
            // Encontrar la grúa
            $grua = Grua::findOrFail($id);

            // Eliminar la grúa
            $grua->delete();

            Log::info('Grúa eliminada exitosamente.', ['grua_id' => $id, 'user_id' => Auth::id()]);

            // Devolver una respuesta de éxito sin contenido
            return response()->json(null, 204); // Código 204 No Content

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              Log::warning('Grúa no encontrada para eliminar.', ['grua_id' => $id, 'user_id' => Auth::id()]);
              return response()->json(['message' => 'Grúa no encontrada.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar grúa: ' . $e->getMessage(), ['exception' => $e, 'grua_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar grúa.', 'error' => $e->getMessage()], 500);
        }
    }

     /**
      * Obtiene una lista simple de grúas (ID y un nombre/identificador).
      * Usado por el frontend para poblar dropdowns en movimientos, etc.
      * Requiere permiso 'view gruas'.
      */
      public function getGruasListSimple()
      {
           // Verificar permiso
          if (!Auth::user()->can('view gruas')) {
               Log::warning('Intento de acceso no autorizado a lista simple de grúas.', ['user_id' => Auth::id()]);
               return response()->json(['message' => 'No tienes permiso para ver la lista de grúas.'], 403);
           }

           try {
               // Selecciona las columnas necesarias para la lista simple
               // ¡CORREGIDO! Usamos 'unidad' y 'tipo' según tu migración
               $gruas = Grua::select('id', 'unidad', 'tipo')
                                   ->get();

               // --- PRIMER LOG: Muestra lo que se recuperó de la BD ---
               Log::info('Datos de grúas recuperados de la BD:', ['gruas' => $gruas->toArray(), 'user_id' => Auth::id()]);
               // --- FIN DEL PRIMER LOG ---

               $gruasList = $gruas->map(function ($grua) {
                    // Crea un nombre legible para mostrar en el dropdown
                    // ¡CORREGIDO! Usamos 'unidad' y 'tipo' para el display_name
                    return [
                        'id' => $grua->id,
                        'display_name' => "{$grua->unidad} - {$grua->tipo}" // Ejemplo de nombre legible
                    ];
               });

                // --- SEGUNDO LOG: Muestra lo que se enviará a Angular (después del map) ---
               Log::info('Datos mapeados de grúas (para enviar a Angular):', ['gruasList' => $gruasList->toArray(), 'user_id' => Auth::id()]);
               // --- FIN DEL SEGUNDO LOG ---


               Log::info('Lista simple de grúas cargada exitosamente.', ['user_id' => Auth::id()]);
               return response()->json($gruasList); // Esto es lo que recibe Angular

           } catch (\Exception $e) {
               Log::error('Error al cargar lista simple de grúas: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
               return response()->json(['message' => 'Error interno del servidor al cargar lista simple de grúas.'], 500);
           }
      }

}