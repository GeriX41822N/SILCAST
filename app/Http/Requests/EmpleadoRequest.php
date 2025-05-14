<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importa Rule
use Illuminate\Support\Facades\Auth; // Importa Auth
use App\Models\Empleado; // Importa el modelo Empleado

class EmpleadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Determina si el usuario está autorizado para hacer esta petición.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Autorizar si el usuario tiene permiso para crear o editar empleados
        // Si es una petición POST (crear), requiere 'create employees'.
        // Si es una petición PUT/PATCH (actualizar), requiere 'edit employees'.
        if ($this->isMethod('POST')) {
            return Auth::check() && Auth::user()->can('create employees');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return Auth::check() && Auth::user()->can('edit employees');
        }

        // Para otros métodos (GET, DELETE), la autorización se maneja en el controlador
        // Permitimos que la petición llegue al controlador para que allí se haga la verificación
        // específica del permiso (ej: 'view employees' para show/index, 'delete employees' para destroy)
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
        $isUpdating = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $empleado = $this->route('empleado'); // Obtén el modelo Empleado inyectado

        $usuarioId = null;
        if ($isUpdating && $empleado) {
            if ($empleado->usuario) {
                $usuarioId = $empleado->usuario->id;
            }
        }

        $rules = [
            // Reglas de validación para los campos del Empleado
            'numero_empleado' => [
                'required',
                'string',
                'max:255',
                // La regla 'unique' debe excluir al empleado actual por su ID al actualizar
                Rule::unique('empleados', 'numero_empleado')->ignore($isUpdating && $empleado ? $empleado->id : null),
            ],
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255', // nullable
            'fecha_nacimiento' => 'required|date',
            'correo_electronico' => [
                'required',
                'string',
                'email',
                'max:255',
                // Opcional: Si el correo electrónico del empleado también debe ser único
                // Rule::unique('empleados', 'correo_electronico')->ignore($isUpdating && $empleado ? $empleado->id : null),
            ],
            'telefono' => 'required|string|max:20', // Ajusta el max según tu necesidad
            'fecha_ingreso' => 'required|date',
            'nss' => 'nullable|string|max:20', // nullable
            'rfc' => 'nullable|string|max:13', // nullable
            'curp' => 'nullable|string|max:18', // nullable
            'calle' => 'required|string|max:255',
            'colonia' => 'required|string|max:255',
            'cp' => 'required|string|max:10', // Ajusta el max
            'municipio' => 'required|string|max:255',
            'clabe' => 'nullable|string|max:18', // nullable
            'banco' => 'nullable|string|max:255', // nullable
            'puesto' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'turno' => 'required|string|max:255',
            'sdr' => 'nullable|string|max:255', // nullable
            'sdr_imss' => 'nullable|string|max:255', // nullable
            'estado' => 'required|string|in:activo,inactivo,baja', // Validar que sea uno de estos valores
            'fecha_baja' => 'nullable|date|after_or_equal:fecha_ingreso', // nullable, y opcionalmente después de fecha_ingreso
            'foto' => 'nullable|string|max:255', // nullable (asumiendo que guardas la ruta/nombre)
            'supervisor_id' => [
                'nullable', // nullable
                'exists:empleados,id', // Debe existir en la tabla empleados, columna id
                // Opcional: Asegurarse de que un empleado no sea su propio supervisor
                // Solo aplicar si $empleadoId no es null (es decir, estamos actualizando un empleado existente)
                Rule::notIn($empleado ? [$empleado->id] : []),
            ],
            'estado_civil' => 'nullable|string|max:255', // nullable

            // Reglas de validación para los campos del Usuario asociado (opcionales)
            // Estos campos solo son requeridos si se intenta crear o actualizar una cuenta de usuario.
            // La validación condicional se basa en si se envían 'email', 'password' o 'roles'.
            'email' => [
                'nullable', // Permitir que sea null si no se gestiona el usuario
                'string',
                'email',
                'max:255',
                // La regla 'unique' debe excluir al usuario actual si estamos actualizando
                Rule::unique('usuarios', 'email')->ignore($usuarioId),
                // Hacer el email requerido si se envía password o roles
                // Esto se maneja con 'required_with'
            ],
            'password' => [
                // El password es requerido solo al CREAR un usuario, o si se envía al ACTUALIZAR
                'nullable', // Permitir que sea null (no se cambia el password al actualizar si está vacío)
                'string',
                'min:8', // Ajusta el mínimo de caracteres
                // Hacer el password requerido si se envía email o roles (al crear)
                // Esto se maneja con 'required_with'
            ],
            // El campo 'roles' se espera como un array de IDs de roles
            'roles' => 'nullable|array', // Puede ser null o un array
            'roles.*' => 'integer|exists:roles,id', // Cada elemento del array debe ser un entero y existir en la tabla 'roles'
        ];

        // Validaciones condicionales:
        // Si se intenta gestionar un usuario (se envía email O password O roles), entonces email y password (al crear) son requeridos.
        // Usamos required_with para que email sea requerido si se envía password o roles, y password sea requerido si se envía email o roles.
        // Al actualizar, el password es opcional si ya existe un usuario.
        $rules['email'][] = 'required_with:password,roles';
        if ($this->isMethod('POST')) {
            $rules['password'][] = 'required_with:email,roles'; // Password es requerido si se envía email o roles (solo al crear)
        }
        // Al actualizar, si se envía password, email es requerido (para asegurar que hay un usuario al que asociarlo)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['email'][] = 'required_with:password';
        }

        return $rules;
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
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'numero_empleado.unique' => 'Este número de empleado ya está registrado.',
            'nombre.required' => 'El nombre del empleado es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.email' => 'El correo electrónico debe ser una dirección válida.',
            'correo_electronico.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso debe ser una fecha válida.',
            'estado.required' => 'El estado del empleado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, inactivo o baja.',
            'fecha_baja.date' => 'La fecha de baja debe ser una fecha válida.',
            'fecha_baja.after_or_equal' => 'La fecha de baja debe ser igual o posterior a la fecha de ingreso.',
            'supervisor_id.exists' => 'El supervisor seleccionado no existe.',
            'supervisor_id.not_in' => 'El empleado no puede ser su propio supervisor.',
            'email.required_with' => 'El email es obligatorio si se proporciona password o roles.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required_with' => 'El password es obligatorio si se proporciona email o roles al crear el usuario.',
            'password.min' => 'El password debe tener al menos :min caracteres.',
            'roles.array' => 'Los roles deben ser un array.',
            'roles.*.integer' => 'Cada rol debe ser un número entero.',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe.',
            'calle.required' => 'La calle es obligatoria.',
            'colonia.required' => 'La colonia es obligatoria.',
            'cp.required' => 'El código postal es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'puesto.required' => 'El puesto es obligatorio.',
            'area.required' => 'El área es obligatoria.',
            'turno.required' => 'El turno es obligatorio.',
        ];
    }
}