// src/app/empleado.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators'; // Importar el operador map

// Importa interfaces necesarias si las usas en otras definiciones dentro de este archivo
import { Role } from './auth.service'; // Asegúrate de la ruta correcta a tu interfaz Role


/**
 * @interface Empleado
 * @description Define la estructura completa de un objeto Empleado tal como se espera del backend.
 * Utilizada para operaciones CRUD completas de empleados.
 */
export interface Empleado {
  id?: number; // Opcional al crear
  numero_empleado: string;
  nombre: string;
  apellido_paterno: string;
  apellido_materno: string | null;
  fecha_nacimiento: string | null;
  correo_electronico: string | null;
  telefono: string | null;
  fecha_ingreso: string | null;
  nss: string | null;
  rfc: string | null;
  curp: string | null;
  calle: string | null;
  colonia: string | null;
  cp: string | null;
  municipio: string | null;
  clabe: string | null;
  banco: string | null;
  puesto: string | null;
  area: string | null;
  turno: string | null;
  sdr: string | null;
  sdr_imss: string | null;
  estado: string | null;
  fecha_baja: string | null;
  foto: string | null;
  supervisor_id: number | null;
  estado_civil: string | null;

  // Si la relación 'usuario' está cargada en el backend
  usuario?: any | null; // Considera definir una interfaz User si necesitas tipado detallado

  created_at?: string;
  updated_at?: string;
}

/**
 * @interface EmpleadoSimple
 * @description Define la estructura simplificada de un objeto Empleado,
 * específicamente para dropdowns o listas compactas.
 * Incluye 'display_name' que combina nombre y apellido.
 */
export interface EmpleadoSimple {
    id: number;
    nombre: string; // Se incluye si es útil tener las partes separadas
    apellido_paterno: string; // Se incluye si es útil tener las partes separadas
    display_name: string; // Propiedad combinada para mostrar en la UI (ej. "Nombre Apellido")
}


/**
 * @class EmpleadoService
 * @description Servicio para interactuar con los endpoints de la API relacionados con los Empleados.
 * Proporciona métodos para operaciones CRUD completas y para obtener listas simplificadas de empleados.
 */
@Injectable({
  providedIn: 'root'
})
export class EmpleadoService {

  // URL base de la API de Laravel. Asegúrate de que coincida con tu configuración local o de despliegue.
  private apiUrl = 'http://localhost:8000/api';

  /**
   * @constructor
   * @param {HttpClient} http - Cliente HTTP de Angular para realizar peticiones.
   */
  constructor(private http: HttpClient) { }

  /**
   * @method getEmpleados
   * @description Obtiene una lista completa de todos los empleados desde el backend.
   * Corresponde al endpoint GET /api/empleados.
   * @returns {Observable<Empleado[]>} Un Observable que emite un array de objetos Empleado.
   */
  getEmpleados(): Observable<Empleado[]> {
    return this.http.get<Empleado[]>(`${this.apiUrl}/empleados`);
  }

  /**
   * @method getEmpleado
   * @description Obtiene los detalles completos de un empleado específico por su ID.
   * Corresponde al endpoint GET /api/empleados/{id}.
   * @param {number} id - El ID del empleado a obtener.
   * @returns {Observable<Empleado>} Un Observable que emite un objeto Empleado.
   */
  getEmpleado(id: number): Observable<Empleado> {
    return this.http.get<Empleado>(`${this.apiUrl}/empleados/${id}`);
  }

  /**
   * @method createEmpleado
   * @description Crea un nuevo registro de empleado en el backend.
   * Corresponde al endpoint POST /api/empleados.
   * @param {any} empleadoData - Los datos del nuevo empleado a crear.
   * @returns {Observable<Empleado>} Un Observable que emite el objeto Empleado creado.
   */
  createEmpleado(empleadoData: any): Observable<Empleado> {
    return this.http.post<Empleado>(`${this.apiUrl}/empleados`, empleadoData);
  }

  /**
   * @method updateEmpleado
   * @description Actualiza un registro de empleado existente en el backend.
   * Corresponde al endpoint PUT/PATCH /api/empleados/{id}.
   * @param {number} id - El ID del empleado a actualizar.
   * @param {any} empleadoData - Los datos actualizados del empleado.
   * @returns {Observable<Empleado>} Un Observable que emite el objeto Empleado actualizado.
   */
  updateEmpleado(id: number, empleadoData: any): Observable<Empleado> {
    return this.http.put<Empleado>(`${this.apiUrl}/empleados/${id}`, empleadoData);
  }

  /**
   * @method deleteEmpleado
   * @description Elimina un registro de empleado del backend.
   * Corresponde al endpoint DELETE /api/empleados/{id}.
   * @param {number} id - El ID del empleado a eliminar.
   * @returns {Observable<void>} Un Observable vacío (<void>).
   */
  deleteEmpleado(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/empleados/${id}`);
  }

  /**
   * @method getEmpleadosListSimple
   * @description Obtiene una lista simplificada de empleados (ID, nombre, apellido_paterno, display_name)
   * para usar en dropdowns y selectores.
   * Corresponde al endpoint GET /api/employees-list en el backend.
   * Mapea los datos recibidos para crear la propiedad 'display_name'.
   * @returns {Observable<EmpleadoSimple[]>} Un Observable que emite un array de objetos EmpleadoSimple.
   */
  getEmpleadosListSimple(): Observable<EmpleadoSimple[]> {
    // Obtenemos los datos como un array de 'any' o una interfaz básica si no tenemos la estructura exacta tipada
    return this.http.get<any[]>(`${this.apiUrl}/employees-list`).pipe(
      // Usamos el operador map para transformar cada objeto en el array
      map(data => {
        // Mapeamos cada item (que tiene id, nombre, apellido_paterno según el console.log)
        return data.map(item => ({
          id: item.id,
          nombre: item.nombre, // Mantenemos si es útil
          apellido_paterno: item.apellido_paterno, // Mantenemos si es útil
          // *** CREAMOS LA PROPIEDAD display_name COMBINANDO nombre y apellido_paterno ***
          display_name: `${item.nombre} ${item.apellido_paterno}`
        }) as EmpleadoSimple); // Casteamos al tipo EmpleadoSimple
      })
      // catchError se puede añadir aquí si necesitas manejar errores específicos para esta llamada
    );
  }

  /**
   * @method getRoles
   * @description Obtiene la lista de roles disponibles desde el backend.
   * **CORRECCIÓN: La ruta es /api/users/roles según tu api.php**
   * @returns {Observable<Role[]>} Un Observable que emite un array de objetos Role.
   */
  getRoles(): Observable<Role[]> {
    return this.http.get<Role[]>(`${this.apiUrl}/users/roles`); // **RUTA ACTUALIZADA**
  }
}