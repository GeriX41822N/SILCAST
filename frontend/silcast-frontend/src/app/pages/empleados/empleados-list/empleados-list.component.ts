import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EmpleadoService, Empleado } from '../../../empleado.service'; // Importa el EmpleadoService y la interfaz Empleado
import { AuthService, User, Role } from '../../../auth.service'; // Importa AuthService y las interfaces de usuario/roles
import { catchError, of } from 'rxjs'; // Importa catchError y of para manejo de errores
import { Router } from '@angular/router';

@Component({
  selector: 'app-empleados-list',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './empleados-list.component.html',
  styleUrl: './empleados-list.component.scss'
})
export class EmpleadosListComponent implements OnInit {

  empleados: Empleado[] = []; // Lista de empleados
  roles: Role[] = []; // Lista de roles disponibles para asignar
  isLoading: boolean = true; // Indicador de carga
  error: string | null = null; // Mensaje de error

  // Propiedad para manejar la creación y edición de un empleado (incluye datos de usuario y roles)
  currentEmpleado: any = { // Usamos 'any' o una interfaz extendida para flexibilidad con campos de empleado, usuario y roles
    id: null, // ID del empleado
    numero_empleado: '', // Número de empleado
    nombre: '', // Nombre del empleado
    apellido_paterno: '', // <-- Añadir campo obligatorio
    apellido_materno: null, // <-- Añadir campo nullable
    fecha_nacimiento: '', // <-- Añadir campo obligatorio (usar formato YYYY-MM-DD)
    correo_electronico: '', // <-- Añadir campo obligatorio (correo del empleado, no el de usuario)
    telefono: '', // <-- Añadir campo obligatorio
    fecha_ingreso: '', // <-- Añadir campo obligatorio (usar formato YYYY-MM-DD)
    nss: null, // <-- Añadir campo nullable
    rfc: null, // <-- Añadir campo nullable
    curp: null, // <-- Añadir campo nullable
    calle: '', // <-- Añadir campo obligatorio
    colonia: '', // <-- Añadir campo obligatorio
    cp: '', // <-- Añadir campo obligatorio
    municipio: '', // <-- Añadir campo obligatorio
    clabe: null, // <-- Añadir campo nullable
    banco: null, // <-- Añadir campo nullable
    puesto: '', // <-- Añadir campo obligatorio
    area: '', // <-- Añadir campo obligatorio
    turno: '', // <-- Añadir campo obligatorio
    sdr: null, // <-- Añadir campo nullable
    sdr_imss: null, // <-- Añadir campo nullable
    estado: 'activo', // <-- Añadir campo con valor por defecto
    fecha_baja: null, // <-- Añadir campo nullable
    foto: null, // <-- Añadir campo nullable
    supervisor_id: null, // <-- Añadir campo nullable
    estado_civil: null, // <-- Añadir campo nullable (o '' si prefieres string vacío)

    // Campos para la cuenta de usuario asociada (opcionales)
    usuario: { // Objeto anidado para los datos del usuario asociado
      id: null, // ID del usuario (si existe)
      email: '', // Email para el login (diferente al correo_electronico del empleado)
      password: '', // Campo para la contraseña (solo se envía si se cambia/crea)
      // No incluimos 'roles' aquí directamente, usamos selectedRoles para el formulario
    },

    // Propiedad para manejar roles seleccionados en el formulario de edición/creación
    selectedRoles: [] as number[] // Array de IDs de roles seleccionados
  };


  // Variables para controlar la visibilidad del formulario y el modo
  showForm: boolean = false; // Controla si el formulario es visible
  isEditing: boolean = false; // True si estamos editando, false si creamos


  constructor(
    private empleadoService: EmpleadoService, // Inyecta el EmpleadoService
    public authService: AuthService, // Inyecta el AuthService (público para usar en el template)
    private router: Router // Inyecta el Router
  ) { }

  ngOnInit(): void {
    console.log('EmpleadosListComponent: Inicializando. Verificando permiso de vista...');
    // Verificar si el usuario tiene permiso para ver empleados
    if (!this.authService.hasPermission('view employees')) {
      console.log('EmpleadosListComponent: No tiene permiso de vista. Redirigiendo al panel de administración.');
      this.router.navigate(['/admin']); // Redirigir al panel
      return; // Importante: detener la carga de datos si no hay permiso
    }

    console.log('EmpleadosListComponent: Permiso de vista concedido. Cargando datos...');
    this.loadEmpleados();
    this.loadRoles();
  }

  // Carga la lista de empleados desde el backend (incluye usuario y roles)
  loadEmpleados(): void {
    // La verificación de permiso 'view employees' se hace en el backend.
    // Si el backend devuelve 403, el interceptor o el catchError lo manejarán.
    this.isLoading = true;
    this.error = null;

    this.empleadoService.getEmpleados().pipe(
        catchError(error => {
            console.error('EmpleadosListComponent: Error al cargar empleados.', error);
             // Manejar error 403 específicamente
            if (error.status === 403) {
                this.error = 'No tienes permiso para ver la lista de empleados.';
            } else {
                this.error = 'Error al cargar los empleados. Por favor, inténtalo de nuevo.';
            }
            this.isLoading = false;
            return of([]); // Devuelve un observable vacío para que la suscripción no falle
        })
    ).subscribe((data: Empleado[]) => {
      console.log('EmpleadosListComponent: Empleados cargados exitosamente.', data);
      this.empleados = data;
      this.isLoading = false;
    });
  }

  // Carga la lista de roles disponibles desde el backend
  loadRoles(): void {
     // La verificación de permiso ('view employees' o 'list roles') se hace en el backend.
     this.empleadoService.getRoles().pipe(
         catchError(error => {
             console.error('EmpleadosListComponent: Error al cargar roles.', error);
             // Puedes manejar errores aquí si la carga de roles requiere un permiso distinto
             this.error = 'Error al cargar los roles disponibles.'; // O un error más específico
             return of([]); // Devuelve un observable vacío de roles
         })
     ).subscribe((data: Role[]) => {
       console.log('EmpleadosListComponent: Roles cargados exitosamente.', data);
       this.roles = data;
     });
  }


  // Muestra el formulario en modo creación para un nuevo empleado
  showCreateForm(): void {
     // Opcional: Verificar permiso en frontend también (aunque el botón estará oculto sin permiso)
     if (!this.authService.hasPermission('create employees')) {
         alert('No tienes permiso para crear empleados.');
         return;
     }
    this.showForm = true;
    this.isEditing = false;
    // Limpiar el objeto para un nuevo empleado y usuario asociado
    this.currentEmpleado = {
        id: null,
        numero_empleado: '',
        nombre: '',
        apellido_paterno: '', // Inicializar campo obligatorio
        apellido_materno: null, // Inicializar campo nullable
        fecha_nacimiento: '', // Inicializar campo obligatorio
        correo_electronico: '', // Inicializar campo obligatorio
        telefono: '', // Inicializar campo obligatorio
        fecha_ingreso: '', // Inicializar campo obligatorio
        nss: null, // Inicializar campo nullable
        rfc: null, // Inicializar campo nullable
        curp: null, // Inicializar campo nullable
        calle: '', // Inicializar campo obligatorio
        colonia: '', // Inicializar campo obligatorio
        cp: '', // Inicializar campo obligatorio
        municipio: '', // Inicializar campo obligatorio
        clabe: null, // Inicializar campo nullable
        banco: null, // Inicializar campo nullable
        puesto: '', // Inicializar campo obligatorio
        area: '', // Inicializar campo obligatorio
        turno: '', // Inicializar campo obligatorio
        sdr: null, // Inicializar campo nullable
        sdr_imss: null, // Inicializar campo nullable
        estado: 'activo', // Inicializar campo con valor por defecto
        fecha_baja: null, // Inicializar campo nullable
        foto: null, // Inicializar campo nullable
        supervisor_id: null, // Inicializar campo nullable
        estado_civil: null, // Inicializar campo nullable

        usuario: { id: null, email: '', password: '' }, // Inicializar objeto usuario
        selectedRoles: [] // Limpiar roles seleccionados
    };
     console.log('Mostrando formulario de creación de empleado.');
  }

  // Muestra el formulario en modo edición para un empleado existente
  editEmpleado(empleado: Empleado): void {
     // Opcional: Verificar permiso en frontend también
     if (!this.authService.hasPermission('edit employees')) {
         alert('No tienes permiso para editar empleados.');
         return;
     }
    this.showForm = true;
    this.isEditing = true;
    // Cargar los datos del empleado a editar en el formulario
    // Hacemos una copia profunda para no modificar el objeto original en la tabla directamente
    this.currentEmpleado = JSON.parse(JSON.stringify(empleado)); // Copia profunda
    // Asegurar que los campos de fecha sean strings en formato YYYY-MM-DD para el input type="date"
    if (this.currentEmpleado.fecha_nacimiento) {
      this.currentEmpleado.fecha_nacimiento = this.formatDateForInput(this.currentEmpleado.fecha_nacimiento);
    }
     if (this.currentEmpleado.fecha_ingreso) {
      this.currentEmpleado.fecha_ingreso = this.formatDateForInput(this.currentEmpleado.fecha_ingreso);
    }
     if (this.currentEmpleado.fecha_baja) {
      this.currentEmpleado.fecha_baja = this.formatDateForInput(this.currentEmpleado.fecha_baja);
    }


    this.currentEmpleado.usuario = this.currentEmpleado.usuario || { id: null, email: '', password: '' }; // Asegurar que el objeto usuario existe
    this.currentEmpleado.usuario.password = ''; // No cargamos el password hasheado, lo dejamos vacío al editar
    // Cargar los IDs de los roles asignados para preseleccionar en el formulario
    this.currentEmpleado.selectedRoles = this.currentEmpleado.usuario && this.currentEmpleado.usuario.roles
                                          ? this.currentEmpleado.usuario.roles.map((role: Role) => role.id)
                                          : [];

     console.log('Editando empleado:', this.currentEmpleado, 'Roles seleccionados:', this.currentEmpleado.selectedRoles);
  }

  // Helper para formatear fechas para input type="date"
  formatDateForInput(dateString: string | null): string | null {
      if (!dateString) return null;
      // Asumiendo que dateString es un formato ISO 8601 (como 'YYYY-MM-DDTHH:mm:ss.sssZ') o 'YYYY-MM-DD'
      // Si es un objeto Date de JS, necesitarías más lógica
      try {
          const date = new Date(dateString);
          // Verifica si la fecha es válida
          if (isNaN(date.getTime())) {
              console.error('Fecha inválida recibida:', dateString);
              return null; // Retorna null o un string vacío si la fecha no es válida
          }
          const year = date.getFullYear();
          const month = ('0' + (date.getMonth() + 1)).slice(-2); // Meses son 0-11
          const day = ('0' + date.getDate()).slice(-2);
          return `${year}-${month}-${day}`;
      } catch (e) {
          console.error('Error al formatear fecha:', dateString, e);
          return null; // En caso de error, retorna null
      }
  }


  // Cancela la edición o creación y oculta el formulario
  cancelEdit(): void {
    this.showForm = false;
    this.isEditing = false;
    // Limpiar el objeto currentEmpleado
    this.currentEmpleado = {
        id: null,
        numero_empleado: '',
        nombre: '',
        apellido_paterno: '',
        apellido_materno: null,
        fecha_nacimiento: '',
        correo_electronico: '',
        telefono: '',
        fecha_ingreso: '',
        nss: null,
        rfc: null,
        curp: null,
        calle: '',
        colonia: '',
        cp: '',
        municipio: '',
        clabe: null,
        banco: null,
        puesto: '',
        area: '',
        turno: '',
        sdr: null,
        sdr_imss: null,
        estado: 'activo',
        fecha_baja: null,
        foto: null,
        supervisor_id: null,
        estado_civil: null,

        usuario: { id: null, email: '', password: '' },
        selectedRoles: []
    };
     console.log('Formulario de empleado cancelado.');
  }

  // Guarda (crea o actualiza) el empleado
  saveEmpleado(): void {
    console.log('Intentando guardar empleado:', this.currentEmpleado);

     // Validar campos básicos del empleado en frontend
     const requiredEmployeeFields = [
         'numero_empleado', 'nombre', 'apellido_paterno', 'fecha_nacimiento',
         'correo_electronico', 'telefono', 'fecha_ingreso', 'calle', 'colonia',
         'cp', 'municipio', 'puesto', 'area', 'turno'
     ];
     for (const field of requiredEmployeeFields) {
         // Verificar si el campo es null, undefined o string vacío (después de trim)
         if (this.currentEmpleado[field] === null || this.currentEmpleado[field] === undefined || (typeof this.currentEmpleado[field] === 'string' && this.currentEmpleado[field].trim() === '')) {
             alert(`El campo "${field}" es obligatorio.`);
             return;
         }
     }


     // Validar campos de usuario si se intenta crear/actualizar cuenta
     // La lógica de validación condicional está en el backend, pero una validación básica en frontend es buena
     const tryingToManageUser = this.currentEmpleado.usuario &&
                                (this.currentEmpleado.usuario.email || this.currentEmpleado.usuario.password || this.currentEmpleado.selectedRoles.length > 0);

     if (tryingToManageUser) {
         if (!this.currentEmpleado.usuario.email || this.currentEmpleado.usuario.email.trim() === '') {
             alert('El Email para la cuenta de usuario es obligatorio si se intenta gestionarla.');
             return;
         }
         // Para creación, password es obligatorio si se intenta crear usuario
         if (!this.isEditing && (!this.currentEmpleado.usuario.password || this.currentEmpleado.usuario.password.trim() === '')) {
              alert('El Password es obligatorio al crear una cuenta de usuario.');
              return;
         }
          // Para edición, si se proporciona password, debe cumplir el mínimo (validación backend)
     }


    // Preparar los datos a enviar al backend
    // Incluir TODOS los campos del empleado (obligatorios y opcionales)
    const empleadoDataToSend: any = {
        numero_empleado: this.currentEmpleado.numero_empleado,
        nombre: this.currentEmpleado.nombre,
        apellido_paterno: this.currentEmpleado.apellido_paterno,
        apellido_materno: this.currentEmpleado.apellido_materno, // nullable
        fecha_nacimiento: this.currentEmpleado.fecha_nacimiento, // YYYY-MM-DD
        correo_electronico: this.currentEmpleado.correo_electronico,
        telefono: this.currentEmpleado.telefono,
        fecha_ingreso: this.currentEmpleado.fecha_ingreso, // YYYY-MM-DD
        nss: this.currentEmpleado.nss, // nullable
        rfc: this.currentEmpleado.rfc, // nullable
        curp: this.currentEmpleado.curp, // nullable
        calle: this.currentEmpleado.calle,
        colonia: this.currentEmpleado.colonia,
        cp: this.currentEmpleado.cp,
        municipio: this.currentEmpleado.municipio,
        clabe: this.currentEmpleado.clabe, // nullable
        banco: this.currentEmpleado.banco, // nullable
        puesto: this.currentEmpleado.puesto,
        area: this.currentEmpleado.area,
        turno: this.currentEmpleado.turno,
        sdr: this.currentEmpleado.sdr, // nullable
        sdr_imss: this.currentEmpleado.sdr_imss, // nullable
        estado: this.currentEmpleado.estado, // default 'activo' pero se puede cambiar
        fecha_baja: this.currentEmpleado.fecha_baja, // nullable (YYYY-MM-DD)
        foto: this.currentEmpleado.foto, // nullable
        supervisor_id: this.currentEmpleado.supervisor_id, // nullable
        estado_civil: this.currentEmpleado.estado_civil, // nullable

        // Incluir datos de usuario SOLO si se intenta gestionar la cuenta
        ...(tryingToManageUser && { // Esto añade las propiedades 'email', 'password', 'roles' si tryingToManageUser es true
            email: this.currentEmpleado.usuario.email,
            // Incluir password SOLO si se proporciona (nuevo o modificado)
            ...(this.currentEmpleado.usuario.password && { password: this.currentEmpleado.usuario.password }),
            // Incluir el array de roles seleccionados
            roles: this.currentEmpleado.selectedRoles
        })
    };

     // Opcional: Limpiar campos nullable si están vacíos para enviar null al backend en lugar de string vacío
     const nullableFields = [
         'apellido_materno', 'nss', 'rfc', 'curp', 'clabe', 'banco', 'sdr',
         'sdr_imss', 'fecha_baja', 'foto', 'supervisor_id', 'estado_civil'
     ];
     for (const field of nullableFields) {
         if (empleadoDataToSend[field] === '') {
             empleadoDataToSend[field] = null;
         }
     }
      // Asegurar que supervisor_id sea null si es 0 o string vacío
     if (empleadoDataToSend.supervisor_id === 0 || empleadoDataToSend.supervisor_id === '') {
         empleadoDataToSend.supervisor_id = null;
     }


    if (this.isEditing) {
      // Lógica para Actualizar
       // Opcional: Verificar permiso en frontend también
       if (!this.authService.hasPermission('edit employees')) {
           alert('No tienes permiso para editar empleados.');
           return;
       }
      if (this.currentEmpleado.id === null) {
           console.error('Error: Intentando actualizar empleado sin ID.');
           alert('Ocurrió un error al intentar actualizar. Faltan datos del empleado.');
           return;
      }

      this.empleadoService.updateEmpleado(this.currentEmpleado.id, empleadoDataToSend).subscribe({
        next: (response: Empleado) => {
          console.log('Empleado actualizado exitosamente:', response);
          alert('Empleado actualizado con éxito!');
          this.cancelEdit(); // Cerrar formulario y resetear estado
          this.loadEmpleados(); // Recargar la lista
        },
        error: (error: any) => {
          console.error('Error al actualizar empleado:', error);
           if (error.status === 403) {
               alert('No tienes permiso para editar empleados.');
          } else if (error.status === 422 && error.error && error.error.errors) {
              let validationErrors = '';
              for (const field in error.error.errors) {
                  validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
              }
              alert('Error de validación al actualizar:\n' + validationErrors);
          } else {
             alert('Ocurrió un error al actualizar el empleado. Por favor, inténtalo de nuevo.');
          }
        }
      });

    } else {
      // Lógica para Crear
       // Opcional: Verificar permiso en frontend también
       if (!this.authService.hasPermission('create employees')) {
           alert('No tienes permiso para crear empleados.');
           return;
       }

       this.empleadoService.createEmpleado(empleadoDataToSend).subscribe({
        next: (response: Empleado) => {
          console.log('Empleado creado exitosamente:', response);
          alert('Empleado creado con éxito!');
          this.cancelEdit(); // Cerrar formulario y resetear estado
          this.loadEmpleados(); // Recargar la lista
        },
        error: (error: any) => {
          console.error('Error al crear empleado:', error);
          if (error.status === 403) {
              alert('No tienes permiso para crear empleados.');
          } else if (error.status === 422 && error.error && error.error.errors) {
              let validationErrors = '';
              for (const field in error.error.errors) {
                  validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
              }
               alert('Error de validación al crear:\n' + validationErrors);
          } else {
             alert('Ocurrió un error al crear el empleado. Por favor, inténtalo de nuevo.');
          }
        }
      });
    }
  }

  // Elimina un empleado (y su usuario asociado)
  deleteEmpleado(id: number): void {
     // Opcional: Verificar permiso en frontend también
     if (!this.authService.hasPermission('delete employees')) {
         alert('No tienes permiso para eliminar empleados.');
         return;
     }

    if (confirm('¿Estás seguro de que deseas eliminar a este empleado? Esto también eliminará su cuenta de usuario asociada (si existe). Esta acción no se puede deshacer.')) {
      console.log('Intentando eliminar empleado con ID:', id);

      this.empleadoService.deleteEmpleado(id).subscribe({
        next: () => { // La respuesta de delete es 204 No Content, no devuelve cuerpo
          console.log('Empleado eliminado exitosamente.');
          alert('Empleado eliminado con éxito!');
          this.loadEmpleados(); // Recargar la lista
           if (this.isEditing && this.currentEmpleado.id === id) {
              this.cancelEdit(); // Cerrar formulario si se elimina el que se estaba editando
          }
        },
        error: (error: any) => {
          console.error('Error al eliminar empleado:', error);
           if (error.status === 403) {
              alert('No tienes permiso para eliminar empleados.');
           } else {
              alert('Ocurrió un error al eliminar el empleado. Por favor, inténtalo de nuevo.');
           }
        }
      });
    } else {
      console.log('Eliminación cancelada por el usuario.');
    }
  }

  // Método para verificar si un rol está seleccionado en el formulario
  isRoleSelected(roleId: number): boolean {
     return this.currentEmpleado.selectedRoles.includes(roleId);
  }

  // Método para manejar la selección/deselección de roles en el formulario (checkboxes)
  onRoleChange(roleId: number, event: any): void {
     if (event.target.checked) {
       // Añadir el rol si está marcado y no está ya en la lista
       if (!this.currentEmpleado.selectedRoles.includes(roleId)) {
         this.currentEmpleado.selectedRoles.push(roleId);
       }
     } else {
       // Eliminar el rol si está desmarcado
       this.currentEmpleado.selectedRoles = this.currentEmpleado.selectedRoles.filter((id: number) => id !== roleId);
     }
      console.log('Roles seleccionados actualizados:', this.currentEmpleado.selectedRoles);
  }

  // Métodos helper para verificar permisos en el template
  canViewEmployees(): boolean { return this.authService.hasPermission('view employees'); }
  canCreateEmployees(): boolean { return this.authService.hasPermission('create employees'); }
  canEditEmployees(): boolean { return this.authService.hasPermission('edit employees'); }
  canDeleteEmployees(): boolean { return this.authService.hasPermission('delete employees'); }
  // Puedes añadir canManageEmployeeUsers() o canAssignRoles() si defines permisos más granulares

}