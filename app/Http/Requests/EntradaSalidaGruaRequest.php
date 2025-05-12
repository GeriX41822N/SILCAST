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
        // Verificar si el usuario está autenticado antes de chequear permisos
        if (!Auth::check()) {
            return false; // No autorizado si no hay usuario loggeado
        }

        // Autorizar según el método de la petición (POST para crear, PUT/PATCH para editar)
        if ($this->isMethod('POST')) {
            return Auth::user()->can('create movements'); // Requiere permiso para crear
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
             // Requiere permiso para editar
            return Auth::user()->can('edit movements');
        }

        // Para otros métodos (ej. GET, DELETE), la autorización general puede ser verdadera
        // y la verificación específica se hace en el controlador si es necesario.
        return true; // Permite que la solicitud pase al controlador para otros métodos
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
            // Validar 'grua_id' - campo obligatorio que debe existir en la tabla 'gruas'
            'grua_id' => [
                'required',
                'integer',
                'exists:gruas,id', // Asegura que la grúa con ese ID exista
            ],
            // Validar 'operador_id' - campo obligatorio que debe existir en la tabla 'empleados'
            // NOTA: El frontend envía 'empleado_id' en el form, el servicio lo mapea a 'operador_id' en el payload.
            // El Request debe validar el nombre que llega en el payload: 'operador_id'.
            'operador_id' => [
                'required', // Asumimos que el operador es obligatorio según tu formulario frontend
                'integer',
                'exists:empleados,id', // Asegura que el empleado (operador) con ese ID exista
            ],
            // Validar 'cliente_id' - campo opcional que debe existir en la tabla 'clientes' si no es nulo
            'cliente_id' => [
                'nullable', // Permite que el cliente sea nulo
                'integer',
                'exists:clientes,id', // Si cliente_id no es nulo, debe existir en 'clientes'
            ],
            // Validar 'tipo_movimiento' - campo obligatorio con valores restringidos
            'tipo_movimiento' => [
                'required',
                'string',
                Rule::in(['entrada', 'salida']), // Solo permite 'entrada' o 'salida'
            ],

            // Reglas para campos condicionalmente obligatorios y formato de fecha/hora
            'fecha_hora_entrada' => [
                Rule::requiredIf($this->input('tipo_movimiento') === 'entrada'), // Requerido solo si tipo_movimiento es 'entrada'
                'nullable', // Permite que sea nulo si no es requerido por la regla requiredIf
                'date', // Valida que el formato sea de fecha/hora válido (Laravel es flexible aquí)
            ],
            'fecha_hora_salida' => [
                Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo si tipo_movimiento es 'salida'
                'nullable', // Permite que sea nulo si no es requerido
                'date', // Valida formato de fecha/hora
            ],

            // Validar 'destino' - campo de ubicación. El frontend envía 'ubicacion', pero la BD/Modelo usa 'destino'.
            // El Request debe validar 'destino' si el servicio lo mapea o si el frontend envía 'destino'.
            // Basado en la migración/modelo, el nombre correcto para la BD es 'destino'.
            // Asumimos que el servicio frontend está enviando 'destino' en el payload.
             'destino' => [
                 Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo si tipo_movimiento es 'salida'
                 'nullable', // Permite que sea nulo si no es requerido
                 'string',
                 'max:255', // Límite de caracteres para el string
             ],

            // Validar 'descripcion' - campo de observaciones.
            // El frontend envía 'descripcion'. Asumimos que el campo existe en la BD y es fillable.
            'descripcion' => 'nullable|string', // Opcional, debe ser string

            // Reglas para campos de kilometraje
            'kilometraje_entrada' => [
                Rule::requiredIf($this->input('tipo_movimiento') === 'entrada'), // Requerido solo para entradas
                'nullable', // Opcional si no es requerido
                'numeric', // Debe ser un número (entero o decimal)
            ],
            'kilometraje_salida' => [
                Rule::requiredIf($this->input('tipo_movimiento') === 'salida'), // Requerido solo para salidas
                'nullable', // Opcional si no es requerido
                'numeric', // Debe ser un número
            ],

            // Reglas para campos de combustible
            'combustible_entrada' => 'nullable|numeric|between:0,100', // Opcional, numérico entre 0 y 100
            'combustible_salida' => 'nullable|numeric|between:0,100', // Opcional, numérico entre 0 y 100

            // Validar 'estado' - Asumimos que el campo existe en BD y es opcional.
            'estado' => 'nullable|string|max:255',

            // Si hay otros campos que el formulario frontend envíe, asegúrate de añadirlos aquí con sus reglas.
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

            'operador_id.required' => 'El campo Empleado (Operador) es obligatorio.', // El mensaje puede seguir refiriéndose al "Empleado" como lo ve el usuario.
            'operador_id.integer' => 'El campo Empleado (Operador) debe ser un número entero válido.',
            'operador_id.exists' => 'El Empleado (Operador) seleccionado no existe.',

            'cliente_id.integer' => 'El campo Cliente debe ser un número entero válido.',
            'cliente_id.exists' => 'El Cliente seleccionado no existe.',

            'tipo_movimiento.required' => 'El campo Tipo de Movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El Tipo de Movimiento debe ser "entrada" o "salida".',

            'fecha_hora_entrada.required_if' => 'El campo Fecha y Hora de Entrada es obligatorio para movimientos de entrada.',
            'fecha_hora_entrada.date' => 'El campo Fecha y Hora de Entrada debe ser una fecha y hora válida.',

            'fecha_hora_salida.required_if' => 'El campo Fecha y Hora de Salida es obligatorio para movimientos de salida.',
            'fecha_hora_salida.date' => 'El campo Fecha y Hora de Salida debe ser una fecha y hora válida.',

            'destino.required_if' => 'El campo Destino es obligatorio para movimientos de salida.', // Mensaje usando 'Destino' según BD
            'destino.string' => 'El campo Destino debe ser una cadena de texto.',
            'destino.max' => 'El campo Destino no debe exceder los :max caracteres.',

            'descripcion.string' => 'El campo Observaciones debe ser una cadena de texto.', // Mensaje usando 'Observaciones' según frontend

            'kilometraje_entrada.required_if' => 'El Kilometraje de Entrada es obligatorio para movimientos de entrada.',
            'kilometraje_entrada.numeric' => 'El Kilometraje de Entrada debe ser un valor numérico.',
            'kilometraje_salida.required_if' => 'El Kilometraje de Salida es obligatorio para movimientos de salida.',
            'kilometraje_salida.numeric' => 'El Kilometraje de Salida debe ser un valor numérico.',

            'combustible_entrada.numeric' => 'El Combustible de Entrada debe ser un valor numérico.',
            'combustible_entrada.between' => 'El Combustible de Entrada debe estar entre :min y :max.',
            'combustible_salida.numeric' => 'El Combustible de Salida debe ser un valor numérico.',
            'combustible_salida.between' => 'El Combustible de Salida debe estar entre :min y :max.',

            'estado.string' => 'El campo Estado debe ser una cadena de texto.',
            'estado.max' => 'El campo Estado no debe exceder los :max caracteres.',
        ];
    }

     /**
      * @method prepareForValidation
      * @description Opcional: Prepara los datos para la validación.
      * Puede usarse para modificar campos antes de que las reglas se apliquen.
      * Por ejemplo, para renombrar 'ubicacion' a 'destino' si el frontend lo envía así.
      * @return void
      */
     /*
     protected function prepareForValidation(): void
     {
         // Si el frontend envía 'ubicacion' y el backend espera 'destino', puedes mapearlo aquí:
         if ($this->has('ubicacion')) {
             $this->merge([
                 'destino' => $this->input('ubicacion'),
             ]);
             $this->request->remove('ubicacion'); // Opcional: remover el campo original
         }

         // Si el frontend envía 'empleado_id' y el backend espera 'operador_id', puedes mapearlo aquí:
         // Pero el servicio frontend ya lo mapea a 'operador_id' en el payload,
         // así que validar 'operador_id' directamente en rules() es suficiente.
     }
     */
}