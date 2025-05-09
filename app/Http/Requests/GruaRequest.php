<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Importa Auth si usas permisos

class GruaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Aquí defines si el usuario actual tiene permiso para realizar la acción
        // que utiliza este Form Request (crear o actualizar una grúa).
        // Por ejemplo, usando Spatie Permissions:

        // Si la ruta es para crear una grúa (POST a /api/gruas)
        if ($this->isMethod('post')) {
            return Auth::check() && Auth::user()->can('create gruas');
        }

        // Si la ruta es para actualizar una grúa (PUT/PATCH a /api/gruas/{id})
        if ($this->isMethod('put') || $this->isMethod('patch')) {
             // Opcional: puedes verificar si el usuario tiene permiso para editar *esta* grúa específica,
             // pero para permisos generales de edición, con 'edit gruas' es suficiente.
            return Auth::check() && Auth::user()->can('edit gruas');
        }

        // Por defecto, denegar si no es una operación reconocida por este request
        return false;

        // Si no estás usando permisos por ahora y solo quieres permitir a usuarios autenticados:
        // return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Reglas para crear y actualizar
            'unidad' => ['required', 'string', 'max:255'], // Unidad es obligatoria
            'tipo' => ['required', 'string', 'max:255'],
            'combustible' => ['required', 'string', 'max:255'],
            'capacidad_toneladas' => ['required', 'numeric', 'between:0.01,999.99'], // Ajusta el rango si es necesario
            'pluma_telescopica_metros' => ['nullable', 'numeric', 'between:0.01,999.99'], // Es nullable en la BD
            'documentacion' => ['nullable', 'string', 'max:255'], // O 'file' si esperas una subida de archivo
            'operador_id' => ['nullable', 'exists:empleados,id'], // Debe existir en la tabla empleados o ser nulo
            'precio_hora' => ['nullable', 'numeric', 'between:0.01,999999.99'], // Ajusta el rango si es necesario
            'ayudante_id' => ['nullable', 'exists:empleados,id'], // Debe existir en la tabla empleados o ser nulo
            'cliente_actual_id' => ['nullable', 'exists:clientes,id'], // Debe existir en la tabla clientes o ser nulo
            'estado' => ['nullable', 'string', 'max:255'], // Puedes añadir reglas específicas si tienes estados predefinidos (ej: 'in:disponible,en_uso,mantenimiento')
        ];

        // Reglas específicas para la creación (POST) - Por ejemplo, 'unidad' debe ser única
        if ($this->isMethod('post')) {
            $rules['unidad'][] = 'unique:gruas,unidad';
        }

        // Reglas específicas para la actualización (PUT/PATCH)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
             // Al actualizar, la unidad debe ser única EXCEPTO para la grúa que estamos actualizando
             $rules['unidad'][] = 'unique:gruas,unidad,' . $this->route('grua'); // Asume que el parámetro de ruta se llama 'grua'
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unidad.required' => 'La unidad/placa es obligatoria.',
            'unidad.unique' => 'Ya existe una grúa con esta unidad/placa.',
            'tipo.required' => 'El tipo de grúa es obligatorio.',
            'combustible.required' => 'El tipo de combustible es obligatorio.',
            'capacidad_toneladas.required' => 'La capacidad en toneladas es obligatoria.',
            'capacidad_toneladas.numeric' => 'La capacidad debe ser un número.',
            'capacidad_toneladas.between' => 'La capacidad debe estar entre :min y :max toneladas.',
            'pluma_telescopica_metros.numeric' => 'La longitud de la pluma debe ser un número.',
            'pluma_telescopica_metros.between' => 'La longitud de la pluma debe estar entre :min y :max metros.',
            'operador_id.exists' => 'El operador seleccionado no es válido.',
            'precio_hora.numeric' => 'El precio por hora debe ser un número.',
             'precio_hora.between' => 'El precio por hora debe estar entre :min y :max.',
            'ayudante_id.exists' => 'El ayudante seleccionado no es válido.',
            'cliente_actual_id.exists' => 'El cliente seleccionado no es válido.',
            // Añade más mensajes personalizados si lo deseas
        ];
    }
}