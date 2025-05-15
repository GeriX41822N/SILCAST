<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario; // Tu modelo de usuario (ajusta el namespace si es diferente)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Para hashear contraseñas
use Illuminate\Support\Facades\Validator; // Para validación manual
use Spatie\Permission\Models\Role; // Para obtener roles de Spatie
use Illuminate\Support\Facades\Auth; // Para verificar permisos del usuario loggeado
use Illuminate\Support\Facades\Log; // Importar Log


/**
 * @class UserController
 * @description Controlador API para la gestión de Usuarios y Roles.
 * Proporciona endpoints para operaciones CRUD de usuarios y la obtención de la lista de roles.
 */
class UserController extends Controller
{

    /**
     * @method index
     * @description Muestra una lista paginada o completa de usuarios.
     * Requiere el permiso 'view users'. Carga las relaciones de roles y permisos.
     * GET /api/users
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) // Añadido $request si se usaran filtros o paginacion mas adelante
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        if (!Auth::user()->can('view users')) {
            Log::warning('Intento de acceso no autorizado a lista de usuarios.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver usuarios.'], 403);
        }
        // ----------------------------

        try {
            // Obtener todos los usuarios cargando sus roles y permisos asociados
            $users = Usuario::with('roles', 'permissions', 'empleado')->get(); // O usar paginacion: Usuario::with('roles', 'permissions')->paginate(10);

            // Ocultar atributos sensibles antes de enviar la respuesta al frontend
            $users->each(function ($user) {
                $user->makeHidden(['password', 'remember_token', 'email_verified_at']); // Oculta email_verified_at si existe
            });

            Log::info('Lista de usuarios cargada exitosamente.', ['count' => $users->count(), 'user_id' => Auth::id()]);
            return response()->json($users);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista de usuarios: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de usuarios.'], 500);
        }
    }

    /**
     * @method getRoles
     * @description Obtiene una lista de todos los roles disponibles en el sistema.
     * Utilizada por el frontend (ej. para llenar dropdowns o checkboxes).
     * Requiere un permiso adecuado (ej. 'manage users' o 'view roles').
     * GET /api/users/roles
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoles(Request $request)
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        // Usa un permiso adecuado para listar roles.
        if (!Auth::user()->can('manage users') && !Auth::user()->can('view roles')) { // Ajusta el/los permiso/s necesarios
             Log::warning('Intento de acceso no autorizado a lista de roles.', ['user_id' => Auth::id()]);
             return response()->json(['message' => 'No tienes permiso para ver la lista de roles.'], 403);
        }
        // ----------------------------

        try {
            // Obtener todos los roles de Spatie. Seleccionamos solo 'id' y 'name' que es lo mínimo necesario para un selector.
            $roles = Role::select('id', 'name')->get();

            Log::info('Lista de roles cargada exitosamente.', ['count' => $roles->count(), 'user_id' => Auth::id()]);
            return response()->json($roles); // Devuelve la lista de roles (ej. [{id: 1, name: 'admin'}, {id: 2, name: 'operator'}])

        } catch (\Exception $e) {
            Log::error('Error al cargar lista de roles: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de roles.'], 500);
        }
    }


    /**
     * @method store
     * @description Crea un nuevo usuario en la base de datos y le asigna roles.
     * Requiere el permiso 'create users'.
     * POST /api/users
     * @param Request $request - Contiene los datos del usuario a crear (email, password, roles, etc.).
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        if (!Auth::user()->can('create users')) {
            Log::warning('Intento de acceso no autorizado a crear usuario.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para crear usuarios.'], 403);
        }
        // ----------------------------

        // Validar los datos de entrada usando Validator facade (o Form Request si prefieres)
        $validator = Validator::make($request->all(), [
            'empleado_id' => 'nullable|integer|exists:empleados,id', // Asegura que empleado_id sea int y exista si no es nulo
            'email' => 'required|string|email|max:255|unique:usuarios,email', // Email obligatorio, formato email, max 255, único en tabla 'usuarios'
            'password' => 'required|string|min:8|max:255', // Contraseña obligatoria, min 8 caracteres, max 255
            'roles' => 'nullable|array', // Espera que 'roles' sea un array (de IDs)
            'roles.*' => 'integer|exists:roles,id', // Cada elemento del array 'roles' debe ser un ID de rol existente
            // Añadir validaciones para otros campos si el modelo Usuario tiene más columnas no relacionadas con empleado
            // 'name' => 'required|string|max:255', // Ejemplo si el usuario tiene campo 'name'
        ]);

        if ($validator->fails()) {
             Log::error('Validacion fallida al crear usuario.', ['errors' => $validator->errors()->toArray(), 'user_id' => Auth::id()]); // toArray() para loguear errores correctamente
             return response()->json(['errors' => $validator->errors()], 422); // Devolver errores de validación
        }

        // Crear el usuario en la base de datos
        try {
            $user = Usuario::create([
                'empleado_id' => $request->empleado_id,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Asignar otros campos del modelo Usuario si aplican
                // 'name' => $request->name, // Ejemplo
            ]);

            // Asignar roles al usuario usando Spatie
            if ($request->has('roles') && is_array($request->roles)) {
                 // syncRoles espera IDs o nombres de roles. Enviamos IDs desde el frontend.
                $user->syncRoles($request->roles);
            }

            // Recargar el usuario con sus relaciones de roles y permisos para la respuesta
            $user->load('roles', 'permissions');
            // Ocultar atributos sensibles
            $user->makeHidden(['password', 'remember_token', 'email_verified_at']);

            Log::info('Usuario creado exitosamente.', ['user_id' => $user->id, 'created_by' => Auth::id()]);
            return response()->json($user, 201); // 201 Created con el objeto usuario creado

        } catch (\Exception $e) {
            Log::error('Error al crear usuario: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id(), 'request_data' => $request->all()]); // Loguear request data si hay error
            return response()->json(['message' => 'Error interno del servidor al crear usuario.'], 500);
        }
    }


    /**
     * @method show
     * @description Muestra los detalles de un usuario específico.
     * Usa Route Model Binding para inyectar la instancia de Usuario.
     * Requiere el permiso 'view users'.
     * GET /api/users/{user}
     * @param Usuario $user - La instancia del usuario resuelta por Route Model Binding.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Usuario $user) // Route Model Binding inyecta la instancia de Usuario
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        // Opcional: permitir que un usuario vea su propio perfil sin el permiso 'view users'
        // if (!Auth::user()->can('view users') && Auth::id() !== $user->id) {
        if (!Auth::user()->can('view users')) {
            Log::warning('Intento de acceso no autorizado a ver usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver este usuario.'], 403);
        }
        // ----------------------------

        try {
            // La instancia del usuario ($user) ya fue cargada por Route Model Binding. Si no se encuentra, Laravel ya devuelve 404.
            // Cargamos relaciones y ocultamos password para la respuesta.
            $user->load('roles', 'permissions');
            $user->makeHidden(['password', 'remember_token', 'email_verified_at']);

            Log::info('Informacion de usuario cargada exitosamente.', ['target_user_id' => $user->id, 'accessed_by' => Auth::id()]);
            return response()->json($user); // 200 OK con los datos del usuario

        } catch (\Exception $e) {
            Log::error('Error al cargar informacion de usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar informacion del usuario.'], 500);
        }
    }


    /**
     * @method update
     * @description Actualiza un usuario existente y sus roles.
     * Usa Route Model Binding para inyectar la instancia de Usuario.
     * Requiere el permiso 'edit users'.
     * PUT/PATCH /api/users/{user}
     * @param Request $request - Contiene los datos actualizados (email, password opcional, roles opcionales, etc.).
     * @param Usuario $user - La instancia del usuario a actualizar.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Usuario $user) // Usa Route Model Binding
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        if (!Auth::user()->can('edit users')) {
            Log::warning('Intento de acceso no autorizado a editar usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para editar usuarios.'], 403);
        }
        // ----------------------------

        // Validar los datos de entrada. La validación de 'email' debe ser única excepto para el usuario actual.
        $validator = Validator::make($request->all(), [
            'empleado_id' => 'nullable|integer|exists:empleados,id', // Ajusta si es required
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $user->id, // Email único (excepto este usuario)
            'password' => 'nullable|string|min:8|max:255', // Contraseña opcional en update, nullable
            'roles' => 'nullable|array', // Espera que 'roles' sea un array (de IDs)
            'roles.*' => 'integer|exists:roles,id', // Cada elemento del array 'roles' debe ser un ID de rol existente
            // Añadir validaciones para otros campos si se actualizan
            // 'name' => 'string|max:255', // Ejemplo si el usuario tiene campo 'name'
        ]);

        if ($validator->fails()) {
             Log::error('Validacion fallida al actualizar usuario.', ['target_user_id' => $user->id, 'errors' => $validator->errors()->toArray(), 'user_id' => Auth::id()]); // toArray() para loguear correctamente
             return response()->json(['errors' => $validator->errors()], 422); // Devolver errores de validación
        }

        // Preparar datos para actualizar. Solo incluir password si viene en la petición.
        $userDataToUpdate = $request->only(['empleado_id', 'email']); // Incluir otros campos actualizables

        if ($request->filled('password')) { // Usar filled() para verificar si el campo existe Y no es vacío
             $userDataToUpdate['password'] = Hash::make($request->password);
        }

        // Actualizar el usuario en la base de datos
        try {
            $user->update($userDataToUpdate);

            // Sincronizar roles (si vienen en la petición)
            if ($request->has('roles') && is_array($request->roles)) {
                 $user->syncRoles($request->roles); // syncRoles espera IDs o nombres
            } elseif ($request->has('roles') && $request->roles === null) {
                 // Si se envía 'roles: null', podrías querer remover todos los roles
                 $user->syncRoles([]);
            }
            // Si 'roles' NO viene en la petición, no se hace nada con los roles existentes.

            // Recargar el usuario con sus relaciones de roles y permisos para la respuesta
            $user->load('roles', 'permissions');
            // Ocultar atributos sensibles
            $user->makeHidden(['password', 'remember_token', 'email_verified_at']);

            Log::info('Usuario actualizado exitosamente.', ['target_user_id' => $user->id, 'updated_by' => Auth::id()]);
            return response()->json($user); // 200 OK con el objeto usuario actualizado

        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id(), 'request_data' => $request->all()]); // Loguear request data si hay error
            return response()->json(['message' => 'Error interno del servidor al actualizar usuario.'], 500);
        }
    }


    /**
     * @method destroy
     * @description Elimina un usuario de la base de datos.
     * Usa Route Model Binding para inyectar la instancia de Usuario.
     * Requiere el permiso 'delete users'.
     * DELETE /api/users/{user}
     * @param Usuario $user - La instancia del usuario a eliminar.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Usuario $user) // Route Model Binding inyecta la instancia de Usuario
    {
        // --- Verificación de Permiso (si no usas middleware en el constructor) ---
        if (!Auth::user()->can('delete users')) {
            Log::warning('Intento de acceso no autorizado a eliminar usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para eliminar usuarios.'], 403);
        }
        // ----------------------------

        try {
            // Eliminar el usuario. Spatie normalmente maneja la eliminación de roles/permisos asociados.
            $user->delete();

            Log::info('Usuario eliminado exitosamente.', ['target_user_id' => $user->id, 'deleted_by' => Auth::id()]);
            return response()->json(null, 204); // 204 No Content para indicar eliminación exitosa sin contenido de respuesta

        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar usuario.'], 500);
        }
    }

    
}