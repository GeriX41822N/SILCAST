<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importa el facade Auth
use App\Models\Usuario; // Importa tu modelo Usuario
// No necesitas importar Role o Permission aquí, ya que Spatie los gestiona a través del usuario

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Valida los datos de la petición (email y password)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intenta autenticar al usuario usando las credenciales
        // Auth::attempt verificará si existe un usuario con ese email y password hasheado
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si la autenticación falla
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401); // Código 401 Unauthorized
        }

        // Si la autenticación es exitosa
        // Obtenemos la instancia del usuario autenticado
        $user = Auth::user(); // Auth::user() ya devuelve una instancia de tu modelo configurado en config/auth.php

        // --- ¡Cargamos manualmente los roles y permisos del usuario aquí! ---
        // El Trait HasRoles añade métodos para cargar estas relaciones
        $user->load('roles', 'permissions'); // <-- ¡Añadimos esta línea!
        // -------------------------------------------------------------------


        // Creamos un token de API para este usuario usando Sanctum
        // 'auth_token' es un nombre descriptivo para el token
        $token = $user->createToken('auth_token')->plainTextToken;

        // --- ¡Incluimos el usuario (con roles y permisos cargados) y el token en la respuesta! ---
        // Devolvemos la respuesta JSON con el token, tipo de token, y el objeto usuario
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // <-- ¡Incluimos el objeto usuario aquí!
        ]);
        // --------------------------------------------------------------------------------------
    }

    /**
     * Log the user out (Revoke the token).
     *
     * @param   \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoca el token actual que se usó para la petición de logout
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}