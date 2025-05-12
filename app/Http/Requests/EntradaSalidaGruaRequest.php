<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EntradaSalidaGruaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autorizar según el método de la petición (POST para crear, PUT/PATCH para editar)
        if ($this->isMethod('POST')) {
            return Auth::check() && Auth::user()->can('create movements');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
             return Auth::check() && Auth::user()->can('edit movements');
        }

        // Permitir otras peticiones (GET, DELETE) pasar al controlador para verificación específica
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'grua_id' => [
                'required',
                'integer',
                'exists:gruas,id', // Asegura que la grúa exista
            ],
            'empleado_id' => [
                'required',
                'integer',
                'exists:empleados,id', // Asegura que el empleado (operador) exista
            ],
            'cliente_id' => [
                 'nullable', // Cliente puede ser nulo
                 'integer',
                 'exists:clientes,id', // Asegura que el cliente exista si no es nulo
            ],
            'tipo_movimiento' => [
                'required',
                'string',
                Rule::in(['entrada', 'salida']), // Asegura que sea 'entrada' o 'salida'
            ],

            // *** Reglas de validación alineadas con los campos del formulario frontend ***
            'fecha_hora_entrada' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'entrada'), // Requerido solo para entradas
                 'nullable', // Permite que sea nulo si no es requerido
                 'date', // Asegura que sea una fecha y hora válida
            ],
            'fecha_hora_salida' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo para salidas
                 'nullable', // Permite que sea nulo si no es requerido
                 'date', // Asegura que sea una fecha y hora válida
            ],

            // El campo 'ubicacion' del frontend (para 'Destino')
             'ubicacion' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo para salidas
                 'nullable', // Permite que sea nulo si no es requerido
                 'string',
                 'max:255',
             ],

            // El campo 'descripcion' del frontend (para 'Observaciones')
             'descripcion' => 'nullable|string',

            // Campos de kilometraje (separados para entrada y salida)
             'kilometraje_entrada' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'entrada'), // Requerido solo para entradas
                 'nullable',
                 'numeric', // Debe ser numérico
             ],
             'kilometraje_salida' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo para salidas
                 'nullable',
                 'numeric', // Debe ser numérico
             ],

            // Campos de combustible (separados para entrada y salida)
             'combustible_entrada' => 'nullable|numeric|between:0,100', // Opcional, numérico entre 0 y 100
             'combustible_salida' => 'nullable|numeric|between:0,100', // Opcional, numérico entre 0 y 100

            'estado' => 'nullable|string|max:255', // Asumimos que 'estado' existe y es opcional

            // Si tienes otros campos que el formulario envíe, añadelos aquí
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'grua_id.required' => 'El campo Grúa es obligatorio.',
            'grua_id.integer' => 'El campo Grúa debe ser un número entero.',
            'grua_id.exists' => 'La Grúa seleccionada no existe.',

            'empleado_id.required' => 'El campo Empleado (Operador) es obligatorio.',
            'empleado_id.integer' => 'El campo Empleado (Operador) debe ser un número entero.',
            'empleado_id.exists' => 'El Empleado (Operador) seleccionado no existe.',

            'cliente_id.integer' => 'El campo Cliente debe ser un número entero.',
            'cliente_id.exists' => 'El Cliente seleccionado no existe.',

            'tipo_movimiento.required' => 'El campo Tipo de Movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El Tipo de Movimiento debe ser "entrada" o "salida".',

            // *** Mensajes de error alineados con los campos del formulario frontend ***
            'fecha_hora_entrada.required_if' => 'El campo Fecha y Hora de Entrada es obligatorio para movimientos de entrada.',
            'fecha_hora_entrada.date' => 'El campo Fecha y Hora de Entrada debe ser una fecha y hora válida.',

            'fecha_hora_salida.required_if' => 'El campo Fecha y Hora de Salida es obligatorio para movimientos de salida.',
            'fecha_hora_salida.date' => 'El campo Fecha y Hora de Salida debe ser una fecha y hora válida.',

            'ubicacion.required_if' => 'El campo Destino es obligatorio para movimientos de salida.',
            'ubicacion.string' => 'El campo Destino debe ser una cadena de texto.',
            'ubicacion.max' => 'El campo Destino no debe exceder los :max caracteres.',

            'descripcion.string' => 'El campo Observaciones debe ser una cadena de texto.',

            // Mensajes para campos de kilometraje
            'kilometraje_entrada.required_if' => 'El Kilometraje de Entrada es obligatorio para movimientos de entrada.',
            'kilometraje_entrada.numeric' => 'El Kilometraje de Entrada debe ser un valor numérico.',
            'kilometraje_salida.required_if' => 'El Kilometraje de Salida es obligatorio para movimientos de salida.',
            'kilometraje_salida.numeric' => 'El Kilometraje de Salida debe ser un valor numérico.',

            // Mensajes para campos de combustible
            'combustible_entrada.numeric' => 'El Combustible de Entrada debe ser un valor numérico.',
            'combustible_entrada.between' => 'El Combustible de Entrada debe estar entre :min y :max.',
            'combustible_salida.numeric' => 'El Combustible de Salida debe ser un valor numérico.',
            'combustible_salida.between' => 'El Combustible de Salida debe estar entre :min y :max.',
        ];
    }
}