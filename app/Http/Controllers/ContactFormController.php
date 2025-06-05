<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Necesitas importar la Facade Mail
use App\Mail\ContactFormMail; // Importa tu clase Mailable

class ContactFormController extends Controller
{
    public function sendEmail(Request $request)
    {
        // 1. Validación de los datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mensaje' => 'required|string|max:2000', // Limita el tamaño del mensaje
        ]);

        // 2. Envío del correo
        try {
            // Envía el correo a melissa.morales@silcast.mx
            Mail::to('melissa.morales@silcast.mx')->send(new ContactFormMail(
                $request->nombre,
                $request->email,
                $request->mensaje
            ));

            // 3. Respuesta exitosa
            return response()->json(['message' => 'Correo enviado con éxito'], 200);

        } catch (\Exception $e) {
            // 4. Manejo de errores
            // Esto es crucial para depuración en producción.
            // Registra el error en los logs de Laravel.
            \Log::error('Error al enviar correo desde el formulario de contacto: ' . $e->getMessage());

            // Puedes retornar un mensaje de error más genérico para el usuario
            return response()->json(['message' => 'Hubo un problema al enviar el correo. Por favor, inténtelo de nuevo más tarde.'], 500);
        }
    }
}