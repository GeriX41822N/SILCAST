// src/app/pages/movimientos/movimientos-list/movimientos-list.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { catchError, of } from 'rxjs';
import { EntradaSalidaGruaService, EntradaSalidaGrua, GruaSimple } from '../../../services/entrada-salida-grua.service';
import { AuthService } from '../../../auth.service';
import { GruaService } from '../../../services/grua.service';

/**
 * @class MovimientosListComponent
 * @description Componente para mostrar la lista de registros de movimientos de grúas.
 * Permite filtrar por rango de fechas de entrada y por grúa.
 * Muestra los campos directamente de la base de datos.
 */
@Component({
  selector: 'app-movimientos-list',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './movimientos-list.component.html',
  styleUrls: ['./movimientos-list.component.scss']
})
export class MovimientosListComponent implements OnInit {

  movimientos: EntradaSalidaGrua[] = [];
  allMovimientos: EntradaSalidaGrua[] = [];
  gruas: GruaSimple[] = [];
  isLoading: boolean = true;
  error: string | null = null;

  filterStartDate: string | null = null;
  filterEndDate: string | null = null;
  selectedGruaId: number | null = null;

  constructor(
    private movimientoService: EntradaSalidaGruaService,
    private gruaService: GruaService,
    public authService: AuthService,
    private router: Router
  ) { }

  ngOnInit(): void {
    console.log('MovimientosListComponent: Inicializando. Verificando permiso de vista...');
    // Verificar si el usuario tiene permiso para ver movimientos
    if (!this.authService.hasPermission('view movements')) {
      console.log('MovimientosListComponent: No tiene permiso de vista. Redirigiendo al panel de administración.');
      this.router.navigate(['/admin']); // Redirigir al panel
      return; // Importante: detener la carga de datos si no hay permiso
    }

    console.log('MovimientosListComponent: Permiso de vista concedido. Cargando datos...');
    this.loadMovimientos();
    this.loadGruasForFilter();
  }

  /**
   * Carga la lista de movimientos desde el backend, aplicando los filtros de fecha y grúa.
   */
  loadMovimientos(): void {
    this.isLoading = true;
    this.error = null;

    this.movimientoService.filterMovimientosByDate(
      this.filterStartDate,
      this.filterEndDate,
      this.selectedGruaId
    ).pipe(
      catchError(error => {
        console.error('MovimientosListComponent: Error al cargar movimientos.', error);
        if (error.status === 403) {
          this.error = 'No tienes permiso para ver la lista de movimientos de grúas.';
        } else {
          this.error = 'Error al cargar los movimientos. Por favor, inténtalo de nuevo.';
        }
        this.isLoading = false;
        this.allMovimientos = [];
        this.movimientos = [];
        return of([]);
      })
    ).subscribe(
      (data: EntradaSalidaGrua[]) => {
        this.allMovimientos = data;
        this.movimientos = data;
        this.isLoading = false;
      }
    );
  }

  /**
   * Carga la lista simplificada de grúas para el dropdown de filtro.
   */
  loadGruasForFilter(): void {
    if (!this.authService.hasPermission('view movements')) {
      this.gruas = [];
      return;
    }

    this.gruaService.getGruasListSimple().pipe(
      catchError(error => {
        console.error('MovimientosListComponent: Error al cargar lista simple de grúas.', error);
        this.gruas = [];
        return of([]);
      })
    ).subscribe(
      (data: GruaSimple[]) => {
        this.gruas = data;
      }
    );
  }

  /**
   * Aplica los filtros de fecha y grúa llamando a loadMovimientos.
   */
  applyFilters(): void {
    this.loadMovimientos();
  }

  /**
   * Limpia todos los filtros y recarga todos los movimientos.
   */
  clearFilters(): void {
    this.filterStartDate = null;
    this.filterEndDate = null;
    this.selectedGruaId = null;
    this.loadMovimientos();
  }

  /**
   * Redirige al formulario de creación de movimiento.
   */
  showCreateForm(): void {
    if (!this.authService.hasPermission('create movements')) {
      alert('No tienes permiso para crear movimientos.');
      return;
    }
    this.router.navigate(['/movimientos/new']);
  }

  /**
   * Redirige al formulario de edición de movimiento.
   * @param movimiento El objeto movimiento a editar.
   */
  editMovimiento(movimiento: EntradaSalidaGrua): void {
    if (!this.authService.hasPermission('edit movements')) {
      alert('No tienes permiso para editar movimientos.');
      return;
    }
    if (movimiento.id === undefined || movimiento.id === null) {
      console.error('MovimientosListComponent: No se puede editar, ID de movimiento no definido.', movimiento);
      alert('No se puede editar este movimiento: ID no válido.');
      return;
    }
    this.router.navigate(['/movimientos/edit', movimiento.id]);
  }

  /**
   * Elimina un registro de movimiento.
   * @param id El ID del movimiento a eliminar.
   */
  deleteMovimiento(id: number): void {
    if (!this.authService.hasPermission('delete movements')) {
      alert('No tienes permiso para eliminar movimientos.');
      return;
    }

    if (confirm('¿Estás seguro de que deseas eliminar este registro de movimiento? Esta acción no se puede deshacer.')) {
      this.movimientoService.deleteMovimiento(id).subscribe({
        next: () => {
          alert('Registro de movimiento eliminado con éxito!');
          this.loadMovimientos(); // Recargar la lista después de eliminar
        },
        error: (error: any) => {
          console.error('MovimientosListComponent: Error al eliminar movimiento:', error);
          if (error.status === 403) {
            alert('No tienes permiso para eliminar movimientos.');
          } else {
            alert('Ocurrió un error al eliminar el registro de movimiento. Por favor, inténtalo de nuevo.');
          }
        }
      });
    }
  }

  canViewMovements(): boolean { return this.authService.hasPermission('view movements'); }
  canCreateMovements(): boolean { return this.authService.hasPermission('create movements'); }
  canEditMovements(): boolean { return this.authService.hasPermission('edit movements'); }
  canDeleteMovements(): boolean { return this.authService.hasPermission('delete movements'); }

}