import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { Router } from '@angular/router';

// Define interfaces para el usuario, rol y permiso (basado en la respuesta del backend)
export interface User {
  id: number;
  empleado_id?: number;
  rol_id?: number; // Si aún usas rol_id, aunque Spatie maneja roles
  email: string;
  name?: string; // Si tu modelo User tiene un campo 'name'
  roles?: Role[]; // Array de roles usando Spatie
  permissions?: Permission[]; // Array de permisos usando Spatie
  empleado?: { // Agrega esta propiedad con la estructura del objeto empleado que recibes del backend
    id: number;
    nombre: string;
    apellido_paterno: string;
    // Agrega aquí cualquier otra propiedad del empleado que necesites (ej: apellido_materno, etc.)
  };
}

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  // permissions?: Permission[]; // Los permisos del rol, si los cargas
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}


@Injectable({
  providedIn: 'root'
})
export class AuthService {

  // Define la URL base de tu API de Laravel
  // Asegúrate de que esta URL sea correcta para tu entorno de desarrollo/producción
  private apiUrl = 'http://localhost:8000/api';
  private tokenKey = 'sanctum_token';
  private userKey = 'current_user';

  // BehaviorSubjects para el estado de login y el usuario actual
  private _isLoggedIn = new BehaviorSubject<boolean>(false);
  private _currentUser = new BehaviorSubject<User | null>(null);

  // Observables públicos para suscribirse a los cambios de estado
  isLoggedIn$ = this._isLoggedIn.asObservable();
  currentUser$ = this._currentUser.asObservable();


  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    // Cargar el estado de autenticación desde localStorage al iniciar el servicio
    this.loadInitialState();
  }

  private loadInitialState(): void {
    const token = this.getToken();
    const user = this.getUserFromStorage();

    if (token && user) {
      // Si se encuentra token y usuario en localStorage, establecer el estado como loggeado
      this._isLoggedIn.next(true);
      this._currentUser.next(user);
      console.log('AuthService: Estado inicial cargado desde localStorage.');
    } else {
      // Si no se encuentra token o usuario, asegurar que el estado sea desloggeado
      this.logoutLocal();
      console.log('AuthService: No se encontró sesión en localStorage.');
    }
  }

  /**
   * Intenta iniciar sesión enviando credenciales al backend.
   * @param email El email del usuario.
   * @param password La contraseña del usuario.
   * @returns Un Observable con la respuesta del backend (debería incluir token y usuario).
   */
  login(email: string, password: string): Observable<any> {
    const credentials = { email, password };
    // *** CORRECCIÓN: Cambiar la URL a /auth/login para que coincida con la ruta de Laravel ***
    return this.http.post<any>(`${this.apiUrl}/auth/login`, credentials).pipe(
      tap(response => {
        // Verificar si la respuesta contiene el token de acceso
        if (response && response.access_token) {
          this.saveToken(response.access_token);
          console.log('AuthService: Token guardado en localStorage.');

          // Verificar si la respuesta contiene el objeto user
          if (response.user) {
            this.saveUser(response.user);
            this._isLoggedIn.next(true);
            this._currentUser.next(response.user);
            console.log('AuthService: Usuario guardado en localStorage y estado actualizado.');
          } else {
            // Si no viene el usuario pero sí el token (caso inusual), loggear advertencia y limpiar
            console.warn('AuthService: Login response did not contain user object.');
            this.logoutLocal();
          }
        } else {
          // Si la respuesta no contiene el token, loggear advertencia y limpiar
          console.warn('AuthService: Login response did not contain access_token.');
          this.logoutLocal();
        }
      }),
      catchError(error => {
        // Manejar errores HTTP durante el login
        console.error('Error en el servicio de login:', error);
        // Limpiar la sesión local en caso de error (ej. credenciales incorrectas)
        this.logoutLocal();
        // Propagar el error para que el componente que llamó a login pueda manejarlo (ej. mostrar mensaje al usuario)
        return throwError(() => error);
      })
    );
  }

  /**
   * Guarda el token de autenticación en localStorage.
   * @param token El token a guardar.
   */
  private saveToken(token: string): void {
    localStorage.setItem(this.tokenKey, token);
  }

  /**
   * Guarda el objeto de usuario en localStorage (como JSON string).
   * @param user El objeto de usuario a guardar.
   */
  private saveUser(user: User): void {
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  /**
   * Obtiene el token de autenticación de localStorage.
   * @returns El token o null si no existe.
   */
  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  /**
   * Obtiene el objeto de usuario de localStorage.
   * @returns El objeto de usuario o null si no existe o hay error al parsear.
   */
  private getUserFromStorage(): User | null {
    const userJson = localStorage.getItem(this.userKey);
    if (userJson) {
      try {
        // Parsear el string JSON a un objeto User
        return JSON.parse(userJson);
      } catch (e) {
        console.error('Error parsing user from localStorage', e);
        // Si hay un error al parsear, limpiar la sesión local
        this.logoutLocal();
        return null;
      }
    }
    return null;
  }

  /**
   * Limpia el token y el usuario de localStorage y actualiza el estado.
   * Método interno para ser llamado después de logout o error.
   */
  private logoutLocal(): void {
      localStorage.removeItem(this.tokenKey);
      localStorage.removeItem(this.userKey);
      this._isLoggedIn.next(false);
      this._currentUser.next(null);
      console.log('AuthService: Limpieza local de sesión completada.');
  }

  /**
   * Intenta cerrar la sesión enviando una petición al backend.
   * @returns Un Observable con la respuesta del backend.
   */
  logout(): Observable<any> {
      const token = this.getToken();
      let headers = new HttpHeaders();
      if (token) {
          // Añadir el token a la cabecera de autorización para la petición de logout
          headers = headers.set('Authorization', 'Bearer ' + token);
      }

      // Enviar petición POST a la ruta de logout en el backend
      return this.http.post<any>(`${this.apiUrl}/auth/logout`, {}, { headers: headers }).pipe(
          tap(() => {
              // Si el logout en backend es exitoso, limpiar el estado local
              console.log('AuthService: Logout en backend exitoso.');
              this.logoutLocal();
          }),
          catchError(error => {
              // Si hay un error en el backend (ej. token inválido), loggear y limpiar estado local de todas formas
              console.error('AuthService: Error durante logout en backend. Limpiando estado local.', error);
              this.logoutLocal();
              // Propagar el error
              return throwError(() => error);
          })
      );
  }


  /**
   * Verifica síncronamente si el usuario está loggeado.
   * Usa el valor actual del BehaviorSubject.
   * @returns True si está loggeado, false en caso contrario.
   */
  isLoggedIn(): boolean {
      return this._isLoggedIn.getValue();
  }

  /**
   * Retorna un Observable del usuario actual.
   * Permite a los componentes suscribirse a los cambios del usuario.
   * @returns Un Observable<User | null>.
   */
  getCurrentUser(): Observable<User | null> {
      return this._currentUser.asObservable();
  }

  /**
   * Retorna el usuario actual de forma síncrona.
   * Útil cuando necesitas el valor actual inmediatamente (ej. en guards).
   * @returns El objeto User o null.
   */
  getCurrentUserSync(): User | null {
      return this._currentUser.getValue();
  }

  /**
   * Verifica si el usuario actual tiene un rol específico.
   * Requiere que los roles se hayan cargado en el objeto User.
   * @param roleName El nombre del rol a verificar.
   * @returns True si el usuario tiene el rol, false en caso contrario.
   */
  hasRole(roleName: string): boolean {
    const user = this._currentUser.getValue();
    // Verificar si el usuario existe y tiene roles
    if (!user || !user.roles) {
      return false;
    }
    // Buscar si algún rol en el array coincide con el nombre
    return user.roles.some(role => role.name === roleName);
  }

  /**
   * Verifica si el usuario actual tiene un permiso específico.
   * Considera también si el usuario es 'super-admin' (que tiene todos los permisos).
   * Requiere que los roles y permisos se hayan cargado en el objeto User.
   * @param permissionName El nombre del permiso a verificar.
   * @returns True si el usuario tiene el permiso (directo o por super-admin), false en caso contrario.
   */
    hasPermission(permissionName: string): boolean {
      const user = this._currentUser.getValue();
      if (!user) {
        return false;
      }

      // Si el usuario tiene el rol 'super-admin', tiene todos los permisos
      if (user.roles?.some(role => role.name === 'super-admin')) {
          return true;
      }

      // Si no es super-admin, verificar si tiene el permiso directo
      if (!user.permissions) {
          return false;
      }

      return user.permissions.some(permission => permission.name === permissionName);
    }

    /**
     * Maneja una respuesta 401 (No Autorizado) limpiando la sesión y redirigiendo al login.
     * Puede ser llamado por un interceptor HTTP.
     */
    handleUnauthorized(): void {
      console.warn('AuthService: Petición no autorizada (401). Limpiando sesión local.');
      this.logoutLocal();
      // Redirigir a la página de login
      this.router.navigate(['/login']);
    }

}