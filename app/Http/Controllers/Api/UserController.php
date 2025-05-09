<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario; // Tu modelo de usuario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Para hashear contraseñas
use Illuminate\Support\Facades\Validator; // Para validación manual
use Spatie\Permission\Models\Role; // Para obtener roles de Spatie
use Illuminate\Support\Facades\Auth; // Para verificar permisos del usuario loggeado
use Illuminate\Support\Facades\Log; // Importar Log para consistencia con el logueo de errores


class UserController extends Controller
{
    // Constructor para aplicar middleware de permisos (opcional pero organizado)
    // public function __construct()
    // {
    //     $this->middleware('permission:view users', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:create users', ['only' => ['store']]);
    //     $this->middleware('permission:edit users', ['only' => ['update']]);
    //     $this->middleware('permission:delete users', ['only' => ['destroy']]);
    // }


    /**
     * Display a listing of the resource (Usuarios).
     */
    public function index()
    {
        // --- Verificacion de Permiso ---
        if (!Auth::user()->can('view users')) {
            Log::warning('Intento de acceso no autorizado a lista de usuarios.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver usuarios.'], 403);
        }
        // ----------------------------

        try { // Añadido try-catch para capturar errores en la carga de usuarios
            // Cargamos las relaciones 'roles' y 'permissions'
            $users = Usuario::with('roles', 'permissions')->get();

            // No queremos enviar el password hasheado al frontend
            $users->each(function ($user) {
                $user->makeHidden(['password', 'remember_token', 'email_verified_at']); // Oculta email_verified_at
            });

            Log::info('Lista de usuarios cargada exitosamente.', ['count' => $users->count()]);
            return response()->json($users);

        } catch (\Exception $e) { // Captura cualquier excepcion durante la carga
            Log::error('Error al cargar lista de usuarios: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de usuarios.'], 500);
        }
    }

    /**
     * Get a list of all available roles.
     * Useful for frontend to display role options.
     */
    public function getRoles()
    {
        // --- Verificacion de Permiso ---
        // Usa un permiso adecuado, como 'manage users' o 'view roles'
        if (!Auth::user()->can('manage users')) { // O 'view roles'
             Log::warning('Intento de acceso no autorizado a lista de roles.', ['user_id' => Auth::id()]);
             return response()->json(['message' => 'No tienes permiso para ver la lista de roles.'], 403);
        }
        // ----------------------------

        try {
            // Obtener todos los roles de Spatie, seleccionando solo id y name
            $roles = Role::select('id', 'name')->get();
            Log::info('Lista de roles cargada exitosamente.', ['count' => $roles->count()]);
            return response()->json($roles);

        } catch (\Exception $e) {
            Log::error('Error al cargar lista de roles: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar la lista de roles.'], 500);
        }
    }


    /**
     * Store a newly created resource in storage (Usuario).
     */
    public function store(Request $request)
    {
        // --- Verificacion de Permiso ---
        if (!Auth::user()->can('create users')) {
            Log::warning('Intento de acceso no autorizado a crear usuario.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para crear usuarios.'], 403);
        }
        // ----------------------------

        // Validar los datos
        $validator = Validator::make($request->all(), [
            // Ajusta 'required' o 'nullable' segun si empleado_id es obligatorio al crear usuario
            'empleado_id' => 'nullable|exists:empleados,id',
            'email' => 'required|email|unique:usuarios,email', // Email unico
            'password' => 'required|string|min:8',
            // ... otras validaciones si hay mas campos de usuario ...
        ]);

        if ($validator->fails()) {
             Log::error('Validacion fallida al crear usuario.', ['errors' => $validator->errors(), 'user_id' => Auth::id()]);
             return response()->json($validator->errors(), 422);
        }

        // Crear el usuario
        try { // Añadido try-catch
            $user = Usuario::create([
                'empleado_id' => $request->empleado_id,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // ... otros campos de usuario si aplican ...
            ]);

            // Asignar roles (si vienen en la peticion)
            if ($request->has('roles')) {
                 // Asegurate que $request->roles es un array valido de nombres o IDs de roles
                 // Puedes añadir validacion para asegurar que los roles existen Role::whereIn('name', $request->roles)->exists()
                $user->syncRoles($request->roles);
            }

            // Cargar relaciones y ocultar password para la respuesta
            $user->load('roles', 'permissions');
            $user->makeHidden(['password', 'remember_token', 'email_verified_at']); // Oculta email_verified_at

            Log::info('Usuario creado exitosamente.', ['user_id' => $user->id, 'created_by' => Auth::id()]);
            return response()->json($user, 201); // 201 Created

        } catch (\Exception $e) {
            Log::error('Error al crear usuario: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al crear usuario.'], 500);
        }
    }


    /**
     * Display the specified resource (Usuario).
     * Usa Route Model Binding.
     */
    public function show(Usuario $user) // Route Model Binding busca el usuario por ID
    {
        // --- Verificacion de Permiso ---
        // Podrias añadir una logica para que un usuario pueda ver su propio perfil sin el permiso 'view users'
        // if (!Auth::user()->can('view users') && Auth::id() !== $user->id) {
        if (!Auth::user()->can('view users')) {
            Log::warning('Intento de acceso no autorizado a ver usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para ver este usuario.'], 403);
        }
        // ----------------------------

        try { // Añadido try-catch
             // La instancia del usuario ($user) ya fue cargada por Route Model Binding.
             // Si Route Model Binding no encuentra el usuario, Laravel ya devuelve 404 antes de llegar aqui.
             // Cargamos las relaciones y ocultamos password para la respuesta.
             $user->load('roles', 'permissions');
             $user->makeHidden(['password', 'remember_token', 'email_verified_at']); // Oculta email_verified_at

             Log::info('Informacion de usuario cargada exitosamente.', ['target_user_id' => $user->id, 'accessed_by' => Auth::id()]);
             return response()->json($user); // 200 OK

        } catch (\Exception $e) {
            Log::error('Error al cargar informacion de usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al cargar informacion del usuario.'], 500);
        }
    }


    /**
     * Update the specified resource in storage (Usuario).
     * Usa Route Model Binding.
     */
    public function update(Request $request, Usuario $user) // Usa Route Model Binding
    {
        // --- Verificacion de Permiso ---
        if (!Auth::user()->can('edit users')) {
            Log::warning('Intento de acceso no autorizado a editar usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para editar usuarios.'], 403);
        }
        // ----------------------------

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'empleado_id' => 'nullable|exists:empleados,id', // Ajusta si es required
            'email' => 'required|email|unique:usuarios,email,' . $user->id, // Email unico (excepto este usuario)
            'password' => 'nullable|string|min:8', // Password opcional en update
            // ... otras validaciones ...
        ]);

        if ($validator->fails()) {
             Log::error('Validacion fallida al actualizar usuario.', ['target_user_id' => $user->id, 'errors' => $validator->errors(), 'user_id' => Auth::id()]);
             return response()->json($validator->errors(), 422);
        }

        // Datos para actualizar
        $userDataToUpdate = $request->only(['empleado_id', 'email']); // Incluir otros campos

        // Hashear password si se proporciona
        if ($request->filled('password')) {
            $userDataToUpdate['password'] = Hash::make($request->password);
        }

        // Actualizar el usuario
        try { // Añadido try-catch
             $user->update($userDataToUpdate);

             // Actualizar roles (si vienen en la peticion)
             if ($request->has('roles')) {
                 // Asegurate que $request->roles es un array valido de nombres o IDs de roles
                 $user->syncRoles($request->roles);
             }

             // Recargar relaciones y ocultar password
             $user->load('roles', 'permissions');
             $user->makeHidden(['password', 'remember_token', 'email_verified_at']); // Oculta email_verified_at

             Log::info('Usuario actualizado exitosamente.', ['target_user_id' => $user->id, 'updated_by' => Auth::id()]);
             return response()->json($user); // 200 OK

        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al actualizar usuario.'], 500);
        }
    }


    /**
     * Remove the specified resource from storage (Usuario).
     * Usa Route Model Binding.
     */
    public function destroy(Usuario $user) // Usa Route Model Binding
    {
        // --- Verificacion de Permiso ---
        if (!Auth::user()->can('delete users')) {
            Log::warning('Intento de acceso no autorizado a eliminar usuario.', ['target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'No tienes permiso para eliminar usuarios.'], 403);
        }
        // ----------------------------

        try { // Añadido try-catch
            // Opcional: Antes de eliminar, podrias querer remover sus roles y permisos explicitamente,
            // aunque Spatie a veces maneja esto en cascada al eliminar el usuario.
            // $user->syncRoles([]); // Remover roles
            // $user->syncPermissions([]); // Remover permisos directos

            // Eliminar el usuario
            $user->delete();

            Log::info('Usuario eliminado exitosamente.', ['target_user_id' => $user->id, 'deleted_by' => Auth::id()]);
            return response()->json(null, 204); // 204 No Content

        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario: ' . $e->getMessage(), ['exception' => $e, 'target_user_id' => $user->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar usuario.'], 500);
        }
    }

    // Puedes añadir un metodo para obtener permisos si necesitas listarlos en el frontend para asignar directamente
    // public function getPermissions() { /* ... logica ... */ }
}