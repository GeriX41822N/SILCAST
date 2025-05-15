<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * @class EntradaSalidaGruaRequest
 * @description Form Request para validar y autorizar las solicitudes
 * de creación y actualización de registros de movimientos de grúas.
 * Asegura que los datos de entrada sean válidos y que el usuario tenga los permisos necesarios.
 */
class EntradaSalidaGruaRequest extends FormRequest
{
    /**
     * @method authorize
     * @description Determina si el usuario autenticado está autorizado
     * para realizar esta solicitud específica (crear o editar movimiento).
     * @return bool True si está autorizado, False en caso contrario.
     */
    public function authorize(): bool
    {
        if (!Auth::check()) {
            return false; // No autorizado si no hay usuario loggeado
        }

        if ($this->isMethod('POST')) {
            return Auth::user()->can('create movements'); // Requiere permiso para crear
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return Auth::user()->can('edit movements'); // Requiere permiso para editar
        }

        return true; // Permite la solicitud para otros métodos
    }

    /**
     * @method rules
     * @description Define las reglas de validación que se aplican a los datos de la solicitud.
     * Los nombres de los campos deben coincidir con los nombres esperados en el payload JSON.
     * @return array Las reglas de validación.
     */
    public function rules(): array
    {
        return [
            'grua_id' => [
                'required',
                'integer',
                'exists:gruas,id',
            ],
            'operador_id' => [
                'nullable',
                'integer',
                'exists:empleados,id',
            ],
            'fecha_hora_entrada' => 'nullable|date',
            'fecha_hora_salida' => 'nullable|date',
            'destino' => 'nullable|string|max:255',
            'kilometraje_entrada' => 'nullable|numeric',
            'kilometraje_salida' => 'nullable|numeric',
            'descripcion' => 'nullable|string',
            'combustible_entrada' => 'nullable|numeric|between:0,100',
            'combustible_salida' => 'nullable|numeric|between:0,100',
            'estado' => 'nullable|string|max:255',
        ];
    }

    /**
     * @method messages
     * @description Personaliza los mensajes de error de validación.
     * @return array Los mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'grua_id.required' => 'El campo Grúa es obligatorio.',
            'grua_id.integer' => 'El campo Grúa debe ser un número entero válido.',
            'grua_id.exists' => 'La Grúa seleccionada no existe.',

            'operador_id.integer' => 'El campo Empleado (Operador) debe ser un número entero válido.',
            'operador_id.exists' => 'El Empleado (Operador) seleccionado no existe.',

            'fecha_hora_entrada.date' => 'El campo Fecha y Hora de Entrada debe ser una fecha y hora válida.',
            'fecha_hora_salida.date' => 'El campo Fecha y Hora de Salida debe ser una fecha y hora válida.',

            'destino.string' => 'El campo Destino debe ser una cadena de texto.',
            'destino.max' => 'El campo Destino no debe exceder los :max caracteres.',

            'kilometraje_entrada.numeric' => 'El Kilometraje de Entrada debe ser un valor numérico.',
            'kilometraje_salida.numeric' => 'El Kilometraje de Salida debe ser un valor numérico.',

            'descripcion.string' => 'El campo Observaciones debe ser una cadena de texto.',

            'combustible_entrada.numeric' => 'El Combustible de Entrada debe ser un valor numérico.',
            'combustible_entrada.between' => 'El Combustible de Entrada debe estar entre :min y :max.',
            'combustible_salida.numeric' => 'El Combustible de Salida debe ser un valor numérico.',
            'combustible_salida.between' => 'El Combustible de Salida debe estar entre :min y :max.',

            'estado.string' => 'El campo Estado debe ser una cadena de texto.',
            'estado.max' => 'El campo Estado no debe exceder los :max caracteres.',
        ];
    }
}