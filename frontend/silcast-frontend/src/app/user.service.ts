// src/app/user.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
// Importa las interfaces necesarias desde AuthService o un archivo de modelos centralizado
import { User, Role, Permission } from './auth.service';

/**
 * @interface UserFormData
 * @description Define la estructura de datos esperada para el formulario de usuario,
 * incluyendo campos como la contraseña y roles seleccionados que pueden no estar
 * directamente en la interfaz `User` del backend.
 */
interface UserFormData {
  id: number | null;
  empleado_id: number | null; // Asumiendo que el formulario permite asociar a un empleado
  email: string;
  password?: string; // La contraseña es opcional al editar
  selectedRoles: number[]; // Array de IDs de roles seleccionados en el formulario
}

/**
 * @class UserService
 * @description Servicio para interactuar con los endpoints de la API relacionados con la gestión de Usuarios y Roles.
 * Proporciona métodos para operaciones CRUD de usuarios y obtener la lista de roles.
 */
@Injectable({
  providedIn: 'root'
})
export class UserService {

  // URL base de la API de Laravel. Asegúrate de que coincida con tu entorno.
  private apiUrl = 'http://localhost:8000/api';

  /**
   * @constructor
   * @param {HttpClient} http - Cliente HTTP de Angular para realizar peticiones.
   */
  constructor(private http: HttpClient) { }

  /**
   * @method getUsers
   * @description Obtiene una lista de todos los usuarios desde el backend.
   * Corresponde al endpoint GET /api/users.
   * @returns {Observable<User[]>} Un Observable que emite un array de objetos User.
   */
  getUsers(): Observable<User[]> {
    // La verificación de permisos ('view users') se realiza en el backend (UserController@index)
    return this.http.get<User[]>(`${this.apiUrl}/users`);
  }

  /**
   * @method getRoles
   * @description Obtiene la lista de todos los roles disponibles desde el backend.
   * Corresponde al endpoint GET /api/users/roles.
   * @returns {Observable<Role[]>} Un Observable que emite un array de objetos Role.
   */
  getRoles(): Observable<Role[]> {
    // La verificación de permisos se realiza en el backend (UserController@getRoles)
    // *** CORRECCIÓN: Cambiar la URL para que apunte a la ruta correcta ***
    return this.http.get<Role[]>(`${this.apiUrl}/users/roles`);
  }

  /**
   * @method getUser
   * @description Obtiene los detalles de un usuario específico por su ID.
   * Corresponde al endpoint GET /api/users/{id}.
   * @param {number} id - El ID del usuario a obtener.
   * @returns {Observable<User>} Un Observable que emite un objeto User.
   */
  getUser(id: number): Observable<User> {
    // La verificación de permisos ('view users') se realiza en el backend (UserController@show)
    return this.http.get<User>(`${this.apiUrl}/users/${id}`);
  }

  /**
   * @method createUser
   * @description Crea un nuevo usuario en el backend.
   * Corresponde al endpoint POST /api/users.
   * @param {any} userData - Los datos del nuevo usuario a crear. Incluye email, password, y un array de IDs de roles ('roles'). Puede incluir 'empleado_id'.
   * @returns {Observable<User>} Un Observable que emite el objeto User creado.
   */
  createUser(userData: any): Observable<User> {
    // La verificación de permisos ('create users') se realiza en el backend (UserController@store)
    return this.http.post<User>(`${this.apiUrl}/users`, userData);
  }

  /**
   * @method updateUser
   * @description Actualiza un usuario existente en el backend.
   * Corresponde al endpoint PUT/PATCH /api/users/{id}.
   * @param {number} id - El ID del usuario a actualizar.
   * @param {any} userData - Los datos actualizados del usuario. Incluye los campos que se desean modificar y un array de IDs de roles ('roles') para sincronizar. Puede incluir 'password' si se cambia, y 'empleado_id'.
   * @returns {Observable<User>} Un Observable que emite el objeto User actualizado.
   */
  updateUser(id: number, userData: any): Observable<User> {
    // La verificación de permisos ('edit users') se realiza en el backend (UserController@update)
    return this.http.put<User>(`${this.apiUrl}/users/${id}`, userData);
  }

  /**
   * @method deleteUser
   * @description Elimina un usuario del backend.
   * Corresponde al endpoint DELETE /api/users/{id}.
   * @param {number} id - El ID del usuario a eliminar.
   * @returns {Observable<void>} Un Observable vacío (<void>) ya que una eliminación exitosa típicamente devuelve 204 No Content.
   */
  deleteUser(id: number): Observable<void> {
    // La verificación de permisos ('delete users') se realiza en el backend (UserController@destroy)
    return this.http.delete<void>(`${this.apiUrl}/users/${id}`);
  }

  // --- Métodos Adicionales (Opcionales) ---
  // Puedes añadir aquí métodos adicionales si necesitas gestionar permisos directos, etc.
  // Por ejemplo, si tu backend tiene endpoints específicos para asignar/revocar roles individualmente:
  // assignRoleToUser(userId: number, roleName: string): Observable<any> { return this.http.post(`${this.apiUrl}/users/${userId}/roles`, { role: roleName }); }
  // removeRoleFromUser(userId: number, roleName: string): Observable<any> { return this.http.delete(`${this.apiUrl}/users/${userId}/roles/${roleName}`); }
}