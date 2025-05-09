<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Importa Auth
use Illuminate\Validation\Rule; // Importa Rule

class EntradaSalidaGruaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Determina si el usuario está autorizado para hacer esta petición.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Autorizar si el usuario tiene permiso para crear o editar movimientos
        // Si es una petición POST (crear), requiere 'create movements'.
        // Si es una petición PUT/PATCH (actualizar), requiere 'edit movements'.
        if ($this->isMethod('POST')) {
            return Auth::check() && Auth::user()->can('create movements');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
             return Auth::check() && Auth::user()->can('edit movements');
        }

        // Para otros métodos (GET, DELETE), la autorización se maneja en el controlador
        // Permitimos que la petición llegue al controlador para verificación más específica
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     * Obtiene las reglas de validación que aplican a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Determinar si estamos creando o actualizando para ajustar reglas si es necesario (menos común para movimientos)
        // $isUpdating = $this->isMethod('PUT') || $this->isMethod('PATCH');
        // $movimientoId = $this->route('movimientos_grua'); // Obtener el ID del movimiento si estamos actualizando

        return [
            // Validaciones para los campos de la tabla 'entradas_salidas_grua'
            'grua_id' => [
                'required',
                'integer',
                'exists:gruas,id', // Asegura que el ID de la grúa exista en la tabla 'gruas'
            ],
            'empleado_id' => [
                'required',
                'integer',
                'exists:empleados,id', // Asegura que el ID del empleado exista en la tabla 'empleados'
            ],
            // Eliminamos la validación para cliente_id
            // 'cliente_id' => [
            //     'nullable', // El cliente_id puede ser nulo (ej. si es una entrada)
            //     'integer',
            //     'exists:clientes,id', // Asegura que el ID del cliente exista si no es nulo
            // ],
            'tipo_movimiento' => [
                'required',
                'string',
                Rule::in(['entrada', 'salida']), // Asegura que sea 'entrada' o 'salida'
            ],
            'fecha_hora' => 'required|date', // Asegura que sea una fecha y hora válida
            'ubicacion_origen' => [
                'required',
                'string',
                'max:255',
                // Opcional: Si la ubicación de origen es requerida solo para entradas
                // Rule::requiredIf($this->input('tipo_movimiento') === 'entrada'),
            ],
            'ubicacion_destino' => [
                'nullable', // La ubicación de destino puede ser nula (ej. si es una entrada)
                'string',
                'max:255',
                // Si la salida es a un lugar genérico y no a un cliente, podrías hacer esto requerido para salidas:
                 Rule::requiredIf($this->input('tipo_movimiento') === 'salida'),
            ],
            'observaciones' => 'nullable|string', // Las observaciones son opcionales
            // Agrega aquí otras reglas para campos adicionales en tu tabla si los tienes
            'kilometraje' => 'nullable|numeric', // Mantener validación de kilometraje
            'combustible_salida' => 'nullable|numeric', // Mantener validación de combustible_salida
            'combustible_entrada' => 'nullable|numeric', // Mantener validación de combustible_entrada
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     * Obtiene los mensajes de error para las reglas de validación definidas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'grua_id.required' => 'El campo Grúa es obligatorio.',
            'grua_id.integer' => 'El campo Grúa debe ser un número entero.',
            'grua_id.exists' => 'La Grúa seleccionada no existe.',

            'empleado_id.required' => 'El campo Empleado (Operador) es obligatorio.', // Ajustado mensaje
            'empleado_id.integer' => 'El campo Empleado (Operador) debe ser un número entero.', // Ajustado mensaje
            'empleado_id.exists' => 'El Empleado (Operador) seleccionado no existe.', // Ajustado mensaje

            // Eliminamos los mensajes de error para cliente_id
            // 'cliente_id.integer' => 'El campo Cliente debe ser un número entero.',
            // 'cliente_id.exists' => 'El Cliente seleccionado no existe.',

            'tipo_movimiento.required' => 'El campo Tipo de Movimiento es obligatorio.',
            'tipo_movimiento.string' => 'El campo Tipo de Movimiento debe ser una cadena de texto.',
            'tipo_movimiento.in' => 'El Tipo de Movimiento debe ser "entrada" o "salida".',

            'fecha_hora.required' => 'El campo Fecha y Hora es obligatorio.',
            'fecha_hora.date' => 'El campo Fecha y Hora debe ser una fecha y hora válida.',

            'ubicacion_origen.required' => 'El campo Ubicación de Origen es obligatorio.',
            'ubicacion_origen.string' => 'El campo Ubicación de Origen debe ser una cadena de texto.',
            'ubicacion_origen.max' => 'El campo Ubicación de Origen no debe exceder los :max caracteres.',

            'ubicacion_destino.required_if' => 'El campo Ubicación de Destino es obligatorio para movimientos de salida.',
            'ubicacion_destino.string' => 'El campo Ubicación de Destino debe ser una cadena de texto.',
            'ubicacion_destino.max' => 'El campo Ubicación de Destino no debe exceder los :max caracteres.',

            'observaciones.string' => 'El campo Observaciones debe ser una cadena de texto.',

            // Mensajes para campos adicionales (mantener)
            'kilometraje.numeric' => 'El kilometraje debe ser un valor numérico.',
            'combustible_salida.numeric' => 'El combustible de salida debe ser un valor numérico.',
            'combustible_entrada.numeric' => 'El combustible de entrada debe ser un valor numérico.',
        ];
    }
}
