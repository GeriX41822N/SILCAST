// src/app/pages/gruas/grua-list/grua-list.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { GruaService, Grua } from '../../../services/grua.service';
import { AuthService } from '../../../auth.service';
import { catchError, of } from 'rxjs';

@Component({
  selector: 'app-grua-list',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './grua-list.component.html',
  styleUrls: ['./grua-list.component.scss']
})
export class GruaListComponent implements OnInit {

  gruas: Grua[] = []; 
  isLoading: boolean = true; // Indicador de carga
  error: string | null = null; // Mensaje de error

  constructor(
    private gruaService: GruaService, 
    public authService: AuthService, 
    private router: Router 
  ) { }

  ngOnInit(): void {
    console.log('GruaListComponent: Inicializando. Verificando permiso de vista...');
    // Verificar si el usuario tiene permiso para ver grúas
    if (!this.authService.hasPermission('view gruas')) {
      console.log('GruaListComponent: No tiene permiso de vista. Redirigiendo al panel de administración.');
      this.router.navigate(['/admin']); // Redirigir al panel
      return; // Importante: detener la carga de datos si no hay permiso
    }

    console.log('GruaListComponent: Permiso de vista concedido. Cargando lista de grúas...');
    this.loadGruas();
  }

  loadGruas(): void {
    this.isLoading = true;
    this.error = null;

    this.gruaService.getGruas().pipe(
      catchError(error => {
        console.error('GruaListComponent: Error al cargar grúas.', error);
         if (error.status === 403) {
            this.error = 'No tienes permiso para ver la lista de grúas.';
         } else {
            this.error = 'Error al cargar la lista de grúas. Por favor, inténtalo de nuevo.';
         }
        this.isLoading = false;
        return of([]);
      })
    ).subscribe(
      (data: Grua[]) => {
        console.log('GruaListComponent: Grúas cargadas exitosamente.', data);
        this.gruas = data; 
        this.isLoading = false;
      }
    );
  }

  showCreateForm(): void {
      console.log('Navegando a formulario de creación de grúa');
      this.router.navigate(['/gruas/new']);
  }

  editGrua(grua: Grua): void {
      console.log('Navegando a formulario de edición para grúa ID:', grua.id);
      this.router.navigate(['/gruas/edit', grua.id]);
  }

  deleteGrua(id: number): void {
      if (confirm('¿Estás seguro de que deseas eliminar esta grúa?')) {
          console.log('GruaListComponent: Eliminando grúa con ID:', id);
          this.isLoading = true; 

          this.gruaService.deleteGrua(id).pipe(
              catchError(error => {
                  console.error('GruaListComponent: Error al eliminar grúa.', error);
                   if (error.status === 403) {
                       alert('No tienes permiso para eliminar grúas.');
                   } else if (error.status === 404) {
                        alert('La grúa que intentas eliminar no fue encontrada.');
                   }
                   else {
                       alert('Error al eliminar la grúa. Por favor, inténtalo de nuevo.');
                   }
                  this.isLoading = false;
                  return of(null); 
              })
          ).subscribe(
              () => {
                  console.log('GruaListComponent: Grúa eliminada exitosamente.', id);
                  alert('Grúa eliminada con éxito.');
                  this.gruas = this.gruas.filter(grua => grua.id !== id);
                  this.isLoading = false;
              }
          );
      } else {
          console.log('GruaListComponent: Eliminación cancelada por el usuario.');
      }
  }
  
  canViewGruas(): boolean {
      return this.authService.hasPermission('view gruas');
  }
  canCreateGruas(): boolean {
       return this.authService.hasPermission('create gruas');
  }
  canEditGruas(): boolean {
      return this.authService.hasPermission('edit gruas');
  }
  canDeleteGruas(): boolean {
      return this.authService.hasPermission('delete gruas');
  }

}