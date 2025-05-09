<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // <-- ¡Importa el facade Auth aquí!

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // --- ¡Verificación de Permiso! ---
        // Solo permite ver la lista si el usuario autenticado tiene el permiso 'view suppliers'
        if (!Auth::user()->can('view suppliers')) {
            return response()->json(['message' => 'No tienes permiso para ver proveedores.'], 403); // 403 Forbidden
        }
        // ----------------------------------

        $proveedores = Proveedor::all();
        return response()->json($proveedores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- ¡Verificación de Permiso! ---
        // Solo permite crear si el usuario autenticado tiene el permiso 'create suppliers'
         if (!Auth::user()->can('create suppliers')) {
            return response()->json(['message' => 'No tienes permiso para crear proveedores.'], 403); // 403 Forbidden
        }
        // ----------------------------------

        // Usamos Validator::make para validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:proveedores,nombre', // Añadido unique
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        // Si la validación falla, devolvemos los errores
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // 422 Unprocessable Entity
        }

        // Si la validación pasa, creamos el proveedor
        $proveedor = Proveedor::create($validator->validated()); // Usar validated() es más seguro
        return response()->json($proveedor, 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(Proveedor $proveedor) // Usa Route Model Binding
    {
         // --- ¡Verificación de Permiso! ---
        // Solo permite ver un proveedor específico si el usuario autenticado tiene el permiso 'view suppliers'
        // Podrías tener un permiso más granular como 'view single supplier' si lo necesitas
         if (!Auth::user()->can('view suppliers')) {
            return response()->json(['message' => 'No tienes permiso para ver este proveedor.'], 403); // 403 Forbidden
        }
        // ----------------------------------

        return response()->json($proveedor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proveedor $proveedor) // Usa Route Model Binding
    {
         // --- ¡Verificación de Permiso! ---
        // Solo permite actualizar si el usuario autenticado tiene el permiso 'edit suppliers'
         if (!Auth::user()->can('edit suppliers')) {
            return response()->json(['message' => 'No tienes permiso para editar proveedores.'], 403); // 403 Forbidden
        }
        // ----------------------------------

        // Usamos Validator::make para validar los datos de actualización
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:proveedores,nombre,' . $proveedor->id, // Ignorar el nombre actual al verificar unicidad
            'contacto' => 'sometimes|nullable|string|max:255',
            'telefono' => 'sometimes|nullable|string|max:20',
            'correo' => 'sometimes|nullable|email|max:255',
            'direccion' => 'sometimes|nullable|string|max:255',
            'notas' => 'sometimes|nullable|string',
        ]);

        // Si la validación falla, devolvemos los errores
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Si la validación pasa, actualizamos el proveedor
        $proveedor->update($validator->validated()); // Usar validated() es más seguro
        return response()->json($proveedor); // 200 OK (por defecto)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedor $proveedor) // Usa Route Model Binding
    {
        // --- ¡Verificación de Permiso! ---
        // Solo permite eliminar si el usuario autenticado tiene el permiso 'delete suppliers'
         if (!Auth::user()->can('delete suppliers')) {
            return response()->json(['message' => 'No tienes permiso para eliminar proveedores.'], 403); // 403 Forbidden
        }
        // ----------------------------------

        $proveedor->delete();
        return response()->json(null, 204); // Devuelve un código de estado 204 No Content
    }
}