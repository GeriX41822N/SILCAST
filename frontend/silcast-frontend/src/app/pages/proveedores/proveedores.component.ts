import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { ProveedoresService, Proveedor } from '../../proveedores.service';
import { AuthService } from '../../auth.service'; 


@Component({
  selector: 'app-proveedores',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './proveedores.component.html',
  styleUrl: './proveedores.component.scss'
})
export class ProveedoresComponent implements OnInit {

  proveedores: Proveedor[] = [];
  isLoading: boolean = true;
  error: string | null = null;

  // Propiedad para manejar la creación y edición
  nuevoProveedor: Proveedor = {
    nombre: '',
    contacto: '',
    telefono: '',
    correo: '',
    direccion: '',
    notas: ''
  };

  // Variables para controlar la visibilidad del formulario y el modo
  showForm: boolean = false; // Controla si el formulario es visible
  isEditing: boolean = false; // True si estamos editando, false si creamos

  constructor(
    private proveedoresService: ProveedoresService,
    public authService: AuthService,
    private router: Router
  ) { }

  ngOnInit(): void {
    console.log('ProveedoresComponent: Inicializando. Verificando permiso de vista...');
    // Verificar si el usuario tiene permiso para ver proveedores
    if (!this.authService.hasPermission('view suppliers')) {
      console.log('ProveedoresComponent: No tiene permiso de vista. Redirigiendo al panel de administración.');
      this.router.navigate(['/admin']); // Redirigir al panel
      return; // Importante: detener la carga de datos si no hay permiso
    }

    console.log('ProveedoresComponent: Permiso de vista concedido. Cargando proveedores...');
    this.loadProveedores();
  }

  loadProveedores(): void {
    this.isLoading = true;
    this.error = null;

    this.proveedoresService.getProveedores().subscribe({
      next: (data: Proveedor[]) => {
        console.log('ProveedoresComponent: Proveedores cargados exitosamente.', data);
        this.proveedores = data;
        this.isLoading = false;
      },
      error: (error: any) => {
        console.error('ProveedoresComponent: Error al cargar proveedores.', error);

        if (error.status === 403) {
            this.error = 'No tienes permiso para ver proveedores.';
        } else {
            this.error = 'Error al cargar los proveedores. Por favor, inténtalo de nuevo.';
        }
        this.isLoading = false;
      }
    });
  }

  // Método para mostrar el formulario en modo creación
  showCreateForm(): void {
 
    if (this.authService.hasPermission('create suppliers')) {
       this.showForm = true;
       this.isEditing = false;
       this.nuevoProveedor = { nombre: '', contacto: '', telefono: '', correo: '', direccion: '', notas: '' };
    } else {
       alert('No tienes permiso para crear proveedores.');
    }
  }

  // Método llamado al hacer clic en "Editar" en la tabla
  editProveedor(proveedor: Proveedor): void {

     if (this.authService.hasPermission('edit suppliers')) {
       this.showForm = true;
       this.isEditing = true;
       this.nuevoProveedor = { ...proveedor };
     } else {
       alert('No tienes permiso para editar proveedores.');
     }
  }

  // Método llamado al hacer clic en "Cancelar"
  cancelEdit(): void {
    this.showForm = false;
    this.isEditing = false;
    this.nuevoProveedor = { nombre: '', contacto: '', telefono: '', correo: '', direccion: '', notas: '' };
  }

  // ----------- Método para guardar (Crear o Actualizar) -----------
  saveProveedor(): void {
    console.log('Intentando guardar proveedor:', this.nuevoProveedor);

    if (!this.nuevoProveedor.nombre) {
      alert('El nombre del proveedor es obligatorio.');
      return;
    }

    if (this.isEditing) {
      // Lógica para Actualizar
      // Opcional: Añadir verificación aquí también
       if (!this.authService.hasPermission('edit suppliers')) {
           alert('No tienes permiso para editar proveedores.');
           return;
       }
      if (this.nuevoProveedor.id === undefined) {
           console.error('Error: Intentando actualizar sin ID de proveedor.');
           alert('Ocurrió un error al intentar actualizar. Faltan datos del proveedor.');
           return;
      }

      this.proveedoresService.updateProveedor(this.nuevoProveedor.id, this.nuevoProveedor).subscribe({
        next: (response: Proveedor) => {
          console.log('Proveedor actualizado exitosamente:', response);
          alert('Proveedor actualizado con éxito!');
          this.cancelEdit();
          this.loadProveedores();
        },
        error: (error: any) => {
          console.error('Error al actualizar proveedor:', error);
          if (error.status === 403) { 
               alert('No tienes permiso para editar proveedores.');
          } else if (error.status === 422 && error.error && error.error.errors) {
              let validationErrors = '';
              for (const field in error.error.errors) {
                  validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
              }
              alert('Error de validación al actualizar:\n' + validationErrors);
          } else {
             alert('Ocurrió un error al actualizar el proveedor. Por favor, inténtalo de nuevo.');
          }
        }
      });

    } else {
      // Lógica para Crear
      // Opcional: Añadir verificación aquí también
       if (!this.authService.hasPermission('create suppliers')) {
           alert('No tienes permiso para crear proveedores.');
           return;
       }
       this.proveedoresService.createProveedor(this.nuevoProveedor).subscribe({
        next: (response: Proveedor) => {
          console.log('Proveedor creado exitosamente:', response);
          alert('Proveedor creado con éxito!');
          this.cancelEdit();
          this.loadProveedores();
        },
        error: (error: any) => {
          console.error('Error al crear proveedor:', error);
          if (error.status === 403) { 
              alert('No tienes permiso para crear proveedores.');
          } else if (error.status === 422 && error.error && error.error.errors) {
              let validationErrors = '';
              for (const field in error.error.errors) {
                  validationErrors += `${field}: ${error.error.errors[field].join(', ')}\n`;
              }
               alert('Error de validación al crear:\n' + validationErrors);
          } else {
             alert('Ocurrió un error al crear el proveedor. Por favor, inténtalo de nuevo.');
          }
        }
      });
    }
  }

  // ----------- Método para eliminar proveedor -----------
  deleteProveedor(id: number): void {
    // Opcional: Añadir verificación aquí también
      if (!this.authService.hasPermission('delete suppliers')) {
          alert('No tienes permiso para eliminar proveedores.');
          return;
      }

    if (confirm('¿Estás seguro de que deseas eliminar este proveedor? Esta acción no se puede deshacer.')) {
      console.log('Intentando eliminar proveedor con ID:', id);

      this.proveedoresService.deleteProveedor(id).subscribe({
        next: (response: any) => {
          console.log('Proveedor eliminado exitosamente:', response);
          alert('Proveedor eliminado con éxito!');
          this.loadProveedores();
           if (this.isEditing && this.nuevoProveedor.id === id) {
              this.cancelEdit();
          }
        },
        error: (error: any) => {
          console.error('Error al eliminar proveedor:', error);
           if (error.status === 403) { 
              alert('No tienes permiso para eliminar proveedores.');
           } else {
              alert('Ocurrió un error al eliminar el proveedor. Por favor, inténtalo de nuevo.');
           }
        }
      });
    } else {
      console.log('Eliminación cancelada por el usuario.');
    }
  }

}