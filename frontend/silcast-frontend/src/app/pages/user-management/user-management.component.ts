// src/app/pages/user-management/user-management.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../user.service';
import { AuthService, User, Role } from '../../auth.service';
import { catchError, of } from 'rxjs';
import { EmpleadoService, EmpleadoSimple } from '../../empleado.service';
import { Router } from '@angular/router';
/**
 * @interface UserFormData
 * @description Define la estructura de datos utilizada por el formulario de gestión de usuarios.
 * Contiene los campos que se vinculan a los inputs del formulario, incluyendo password
 * y un array de IDs para los roles seleccionados.
 */
interface UserFormData {
  id: number | null;
  empleado_id: number | null;
  email: string;
  password?: string;
  selectedRoles: number[]; 
}


/**
 * @class UserManagementComponent
 * @description Componente para la gestión (CRUD) de usuarios y la asignación de roles.
 * Muestra una lista de usuarios y permite crear, editar y eliminar usuarios,
 * así como modificar sus roles utilizando un formulario.
 */
@Component({
  selector: 'app-user-management',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './user-management.component.html',
  styleUrl: './user-management.component.scss'
})
export class UserManagementComponent implements OnInit {

  users: User[] = []; // Lista de usuarios obtenida del backend (cada User aquí PUEDE incluir un array de roles)
  roles: Role[] = []; // Lista de todos los roles disponibles para seleccionar en el formulario
  empleados: EmpleadoSimple[] = []; // Lista de empleados para el dropdown
  isLoading: boolean = true; // Indica si se están cargando datos
  error: string | null = null; // Almacena mensajes de error

  // Objeto para gestionar el usuario actualmente seleccionado para creación o edición en el formulario.
  // Tipado con la interfaz UserFormData para mayor claridad y seguridad.
  currentUser: UserFormData = {
    id: null,
    empleado_id: null, // Campo para asociar a un empleado si aplica
    email: '',
    password: '', // Inicialmente vacío, se llena al crear o si se cambia en edición
    selectedRoles: [] // Inicialmente vacío, se llena con IDs al editar
  };

  showForm: boolean = false; // Controla la visibilidad del formulario
  isEditing: boolean = false; // Indica si el formulario está en modo edición


  /**
   * @constructor
   * @param {UserService} userService - Servicio para interactuar con los endpoints de usuarios y roles.
   * @param {AuthService} authService - Servicio de autenticación para verificar permisos.
   * @param {EmpleadoService} empleadoService - Servicio para interactuar con los empleados.
   */
  constructor(
    private userService: UserService,
    public authService: AuthService,
    private empleadoService: EmpleadoService,
    private router: Router
  ) { }

  /**
   * @method ngOnInit
   * @description Método del ciclo de vida que se ejecuta al inicializar el componente.
   * Llama a los métodos para cargar la lista de usuarios y roles disponibles.
   */
  ngOnInit(): void {
    console.log('UserManagementComponent: Inicializando. Verificando permiso de vista...');
    // Verificar si el usuario tiene permiso para gestionar usuarios
    if (!this.authService.hasPermission('manage users')) {
      console.log('UserManagementComponent: No tiene permiso. Redirigiendo al panel de administración.');
      this.router.navigate(['/admin']); // Redirigir al panel
      return; // Importante: detener la carga de datos si no hay permiso
    }

    console.log('UserManagementComponent: Permiso concedido. Cargando datos...');
    this.loadUsers();
    this.loadRoles();
    this.loadEmpleadosForDropdown();
  }
  /**
   * @method loadUsers
   * @description Carga la lista de usuarios desde el backend.
   * Se espera que el backend incluya las relaciones (ej. roles) si son necesarias.
   * Maneja la carga, indicadores de estado y errores (incluido 403 Forbidden).
   * Corresponde al endpoint GET /api/users.
   */
  loadUsers(): void {
    this.isLoading = true;
    this.error = null;

    this.userService.getUsers().pipe(
      catchError(error => {
        console.error('UserManagementComponent: Error al cargar usuarios.', error);
        if (error.status === 403) {
          this.error = 'No tienes permiso para ver la lista de usuarios.';
        } else {
          this.error = 'Error al cargar los usuarios. Por favor, inténtalo de nuevo.';
        }
        this.isLoading = false;
        return of([]);
      })
    ).subscribe((data: User[]) => {
      console.log('UserManagementComponent: Usuarios cargados exitosamente.', data);
      this.users = data;
      this.isLoading = false;
    });
  }

  /**
   * @method loadRoles
   * @description Carga la lista de todos los roles disponibles desde el backend.
   * Utilizada para poblar las opciones de selección de roles en el formulario.
   * Corresponde al endpoint GET /api/users/roles.
   * Asegúrate de que userService.getRoles() llame a la URL correcta.
   */
  loadRoles(): void {
    // La verificación de permisos se realiza en el backend (UserController@getRoles)
    this.userService.getRoles().pipe(
      catchError(error => {
        console.error('UserManagementComponent: Error al cargar roles.', error);
        this.error = 'Error al cargar los roles disponibles.';
        return of([]);
      })
    ).subscribe((data: Role[]) => {
      console.log('UserManagementComponent: Roles cargados exitosamente.', data);
      this.roles = data;
    });
  }

  /**
   * @method loadEmpleadosForDropdown
   * @description Carga la lista simple de empleados para el dropdown del formulario.
   * Utiliza el servicio EmpleadoService para obtener los datos.
   */
  loadEmpleadosForDropdown(): void {
    this.empleadoService.getEmpleadosListSimple().pipe(
      catchError(error => {
        console.error('UserManagementComponent: Error al cargar la lista simple de empleados.', error);
        this.error = 'Error al cargar la lista de empleados.';
        return of([]);
      })
    ).subscribe(data => {
      this.empleados = data;
      console.log('UserManagementComponent: Lista simple de empleados cargada para el dropdown.', this.empleados);
    });
  }

  /**
   * @method showCreateForm
   * @description Prepara y muestra el formulario para crear un nuevo usuario.
   * Inicializa `currentUser` y establece `isEditing` a false.
   */
  showCreateForm(): void {
    if (!this.authService.hasPermission('create users')) {
      alert('No tienes permiso para crear usuarios.');
      return;
    }
    this.showForm = true;
    this.isEditing = false;
    // Inicializar currentUser con valores para creación
    this.currentUser = { id: null, empleado_id: null, email: '', password: '', selectedRoles: [] };
    console.log('Mostrando formulario de creación de usuario.');
  }

  /**
   * @method editUser
   * @description Carga los datos de un usuario existente en el formulario para edición.
   * Mapea los datos del objeto User (con array de Role objects) a UserFormData (con array de role IDs).
   * @param {User} user - El objeto User a editar (generalmente de la lista `users`).
   */
  editUser(user: User): void {
    if (!this.authService.hasPermission('edit users')) {
      alert('No tienes permiso para editar usuarios.');
      return;
    }
    this.showForm = true;
    this.isEditing = true;
    // Mapear los datos del objeto User recibido del backend a la estructura UserFormData del formulario.
    this.currentUser = {
      id: user.id,
      empleado_id: user.empleado_id || null, // Asumiendo que User tiene empleado_id o es null
      email: user.email,
      password: '', // La contraseña nunca se carga para edición
      selectedRoles: user.roles ? user.roles.map(role => role.id) : [] // Extraer IDs de roles
    };
    console.log('Editando usuario:', this.currentUser, 'Roles seleccionados al editar:', this.currentUser.selectedRoles);
  }

  /**
   * @method cancelEdit
   * @description Oculta el formulario y reinicia el estado de `currentUser`.
   */
  cancelEdit(): void {
    this.showForm = false;
    this.isEditing = false;
    // Reinicializar currentUser al cancelar
    this.currentUser = { id: null, empleado_id: null, email: '', password: '', selectedRoles: [] };
    console.log('Formulario de usuario cancelado.');
  }

  /**
   * @method saveUser
   * @description Guarda (crea o actualiza) el usuario llamando al servicio correspondiente.
   * Prepara los datos (`userDataToSend`) desde `currentUser` para enviarlos al backend.
   */
  saveUser(): void {
    console.log('Intentando guardar usuario:', this.currentUser, 'Roles seleccionados:', this.currentUser.selectedRoles);

    // Validaciones básicas en frontend
    if (!this.currentUser.email || (!this.isEditing && !this.currentUser.password)) {
      alert('Email y Password (para nuevo usuario) son obligatorios.');
      return;
    }
    // Si el backend requiere empleado_id para nuevos usuarios y no está lleno
    // if (!this.isEditing && (this.currentUser.empleado_id === null || this.currentUser.empleado_id === undefined)) {
    //    alert('Debe asociar un empleado al nuevo usuario.');
    //    return;
    // }


    // Preparar los datos a enviar al backend. Usamos la estructura que el backend espera en el Request.
    const userDataToSend: any = { // Usamos 'any' o una interfaz de DTO si la estructura es muy diferente a UserFormData
      email: this.currentUser.email,
      // Incluir password SOLO si se proporciona un valor (en creación o si se modifica en edición)
      ...(this.currentUser.password && { password: this.currentUser.password }),
      // Incluir el array de IDs de roles seleccionados bajo la clave 'roles' (como espera el backend para sincronizar)
      roles: this.currentUser.selectedRoles, // Envía un array de IDs (ej. [1, 3])
      // Incluir empleado_id si tiene valor
      ...(this.currentUser.empleado_id !== null && this.currentUser.empleado_id !== undefined && { empleado_id: this.currentUser.empleado_id }),
      // Añadir otros campos si el backend los espera (ej. 'name' si no se gestiona por empleado)
    };

    if (this.isEditing) {
      if (!this.authService.hasPermission('edit users')) {
        alert('No tienes permiso para editar usuarios.'); return;
      }
      if (this.currentUser.id === null) {
        console.error('Error: Intentando actualizar sin ID de usuario.');
        alert('Ocurrió un error al intentar actualizar. Faltan datos del usuario.'); return;
      }

      this.userService.updateUser(this.currentUser.id, userDataToSend).subscribe({
        next: (response: User) => {
          console.log('Usuario actualizado exitosamente:', response);
          alert('Usuario actualizado con éxito!');
          this.cancelEdit();
          this.loadUsers();
        },
        error: (error: any) => {
          console.error('Error al actualizar usuario:', error);
          if (error.status === 403) {
            alert('No tienes permiso para editar usuarios.');
          } else if (error.status === 422 && error.error?.errors) {
            let validationErrors = '';
            for (const field in error.error.errors) {
              validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
            }
            alert('Error de validación al actualizar:\n' + validationErrors);
          } else {
            alert('Ocurrió un error al actualizar el usuario. Por favor, inténtalo de nuevo.');
          }
        }
      });

    } else { // Creando usuario
      if (!this.authService.hasPermission('create users')) {
        alert('No tienes permiso para crear usuarios.'); return;
      }

      this.userService.createUser(userDataToSend).subscribe({
        next: (response: User) => {
          console.log('Usuario creado exitosamente:', response);
          alert('Usuario creado con éxito!');
          this.cancelEdit();
          this.loadUsers();
        },
        error: (error: any) => {
          console.error('Error al crear usuario:', error);
          if (error.status === 403) {
            alert('No tienes permiso para crear usuarios.');
          } else if (error.status === 422 && error.error?.errors) {
            let validationErrors = '';
            for (const field in error.error.errors) {
              validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
            }
            alert('Error de validación al crear:\n' + validationErrors);
          } else {
            alert('Ocurrió un error al crear el usuario. Por favor, inténtalo de nuevo.');
          }
        }
      });
    }
  }

  /**
   * @method deleteUser
   * @description Elimina un usuario existente.
   * @param {number} id - El ID del usuario a eliminar.
   */
  deleteUser(id: number): void {
    if (!this.authService.hasPermission('delete users')) {
      alert('No tienes permiso para eliminar usuarios.');
      return;
    }

    if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
      console.log('Intentando eliminar usuario con ID:', id);

      this.userService.deleteUser(id).subscribe({
        next: () => {
          console.log('Usuario eliminado exitosamente.');
          alert('Usuario eliminado con éxito!');
          this.loadUsers();
          if (this.isEditing && this.currentUser.id === id) {
            this.cancelEdit();
          }
        },
        error: (error: any) => {
          console.error('Error al eliminar usuario:', error);
          if (error.status === 403) {
            alert('No tienes permiso para eliminar usuarios.');
          } else {
            alert('Ocurrió un error al eliminar el usuario. Por favor, inténtalo de nuevo.');
          }
        }
      });
    } else {
      console.log('Eliminación cancelada por el usuario.');
    }
  }

  /**
   * @method isRoleSelected
   * @description Verifica si un rol específico está seleccionado en el formulario.
   * @param {number} roleId - El ID del rol a verificar.
   * @returns {boolean} - True si el rol está en `currentUser.selectedRoles`.
   */
  isRoleSelected(roleId: number): boolean {
    return this.currentUser.selectedRoles.includes(roleId);
  }

  /**
   * @method onRoleChange
   * @description Maneja el evento de cambio en los checkboxes de roles.
   * Añade o elimina el ID del rol del array `currentUser.selectedRoles`.
   * @param {number} roleId - El ID del rol.
   * @param {any} event - El evento del DOM del checkbox.
   */
  onRoleChange(roleId: number, event: any): void {
    if (event.target.checked) {
      if (!this.currentUser.selectedRoles.includes(roleId)) {
        this.currentUser.selectedRoles.push(roleId);
      }
    } else {
      this.currentUser.selectedRoles = this.currentUser.selectedRoles.filter(id => id !== roleId);
    }
    console.log('Roles seleccionados actualizados:', this.currentUser.selectedRoles);
  }

  // --- Métodos Helper para Permisos en el Template ---
  canViewUsers(): boolean { return this.authService.hasPermission('view users'); }
  canCreateUsers(): boolean { return this.authService.hasPermission('create users'); }
  canEditUsers(): boolean { return this.authService.hasPermission('edit users'); }
  canDeleteUsers(): boolean { return this.authService.hasPermission('delete users'); }
  // canAssignRoles(): boolean { return this.authService.hasPermission('assign roles'); }
}