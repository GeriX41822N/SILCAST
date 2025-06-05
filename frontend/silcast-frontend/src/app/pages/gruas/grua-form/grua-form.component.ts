// src/app/pages/gruas/grua-form/grua-form.component.ts
import { Component, OnInit, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

// Importar servicios y interfaces necesarias
import { GruaService, Grua } from '../../../services/grua.service';
// import { AuthService } from '../../../auth.service'; // Si no lo usas directamente, puedes quitarlo

// Importar el servicio de Empleado y la interfaz EmpleadoSimple
import { EmpleadoService, EmpleadoSimple } from '../../../empleado.service';

// TODO: Define esta interfaz ClienteSimple si la necesitas m\u00E1s adelante
// import { ClienteService, ClienteSimple } from '../../../services/cliente.service'; // Importa ClienteService e interfaz


import { ActivatedRoute, Router } from '@angular/router';

import { catchError, of, Observable } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';


@Component({
  selector: 'app-grua-form',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './grua-form.component.html',
  styleUrls: ['./grua-form.component.scss']
})
export class GruaFormComponent implements OnInit {

  gruaId: number | null = null;
  isEditing: boolean = false;

  grua: Grua = {
    id: 0,
    unidad: '',
    tipo: 'Grúa Titán',
    combustible: 'Diesel', // \u00a1Inicializamos con Diesel por defecto para el selector!
    capacidad_toneladas: 0,
    pluma_telescopica_metros: null,
    documentacion: null,
    operador_id: null,
    precio_hora: null,
    ayudante_id: null,
    cliente_actual_id: null,
    estado: 'disponible',
  };

  isLoading: boolean = false;
  generalError: string | null = null;
  validationErrors: any | null = null;

  // Propiedades para el mensaje de \u00E9xito (sin Toastr)
  showSuccessMessage: boolean = false;
  successMessageText: string = '';


  // Propiedades para almacenar listas para dropdowns
  operadores: EmpleadoSimple[] = [];
  // clientes: ClienteSimple[] = []; // Propiedad para clientes (pendiente)

  // Lista de tipos de gr\u00FAa para el selector
  tiposGrua: string[] = ['Grúa Titán'];

  // Lista de tipos de combustible para el selector
  tiposCombustible: string[] = ['Diesel', 'Gasolina'];


  constructor(
    private gruaService: GruaService,
    private empleadoService: EmpleadoService,
    // private clienteService: ClienteService, // Pendiente
    // public authService: AuthService, // Si no lo usas, puedes quitarlo
    private route: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.validationErrors = null;
    this.generalError = null;
    this.hideSuccessMessage(); // Asegurarse de que el mensaje de \u00E9xito est\u00E9 oculto al iniciar

    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.gruaId = +id;
        this.isEditing = true;
        console.log('GruaFormComponent: Modo edici\u00F3n. Cargando grúa ID:', this.gruaId);
        this.loadGruaForEditing(this.gruaId);
      } else {
        this.isEditing = false;
        console.log('GruaFormComponent: Modo creaci\u00F3n.');
      }
    });

    this.loadOperadores();
    // this.loadClientes(); // Carga de clientes (pendiente)
  }

  loadGruaForEditing(id: number): void {
    this.isLoading = true;
    this.generalError = null;
    this.validationErrors = null;
    this.hideSuccessMessage(); // Ocultar mensaje de \u00E9xito

    this.gruaService.getGruaById(id).pipe(
      catchError((error: HttpErrorResponse) => {
        console.error('GruaFormComponent: Error al cargar grúa para edición.', error);
         this.isLoading = false;
        if (error.status === 404) {
          this.generalError = 'La grúa solicitada no fue encontrada.';
        } else if (error.status === 403) {
          this.generalError = 'No tienes permiso para ver esta grúa.';
        } else {
          this.generalError = 'Error al cargar los datos de la grúa. Por favor, inténtalo de nuevo.';
        }
        return of(null);
      })
    ).subscribe(
      (data: Grua | null) => {
        if (data) {
           console.log('GruaFormComponent: Datos de grúa para edición cargados exitosamente.', data);
           this.grua = data;
        }
        this.isLoading = false;
      }
    );
  }

   loadOperadores(): void {
       console.log('GruaFormComponent: Cargando lista de operadores desde el servicio...');
        this.empleadoService.getEmpleadosListSimple().pipe(
          catchError((error: HttpErrorResponse) => {
            console.error('Error cargando operadores:', error);
            this.generalError = `No se pudo cargar la lista de operadores/ayudantes. Código de error: ${error.status}`;
            return of([]);
          })
        ).subscribe(data => {
            this.operadores = data;
            console.log('GruaFormComponent: Operadores cargados desde servicio:', data);
        });
   }

   // TODO: M\u00E9todo para cargar la lista simple de clientes (Pendiente)
   // loadClientes(): void { ... }


  // M\u00E9todo llamado al enviar el formulario
  saveGrua(): void {
    this.isLoading = true;
    this.generalError = null;
    this.validationErrors = null;
    this.hideSuccessMessage(); // Ocultar mensaje de \u00E9xito al intentar guardar


    let saveObservable: Observable<Grua>;
    // Mensajes de \u00E9xito y error m\u00E1s claros
    const successMessageText = this.isEditing ? '\u00a1Grúa actualizada con éxito!' : '\u00a1Grúa registrada con éxito!';
    const errorMessage = this.isEditing ? 'Error al actualizar la grúa.' : 'Error al registrar la grúa.';


    if (this.isEditing && this.gruaId !== null) {
      console.log('GruaFormComponent: Guardando cambios para grúa ID:', this.gruaId, this.grua);
      saveObservable = this.gruaService.updateGrua(this.gruaId, this.grua);
    } else {
       const gruaToCreate = { ...this.grua, id: undefined };
      console.log('GruaFormComponent: Creando nueva grúa:', gruaToCreate);
      saveObservable = this.gruaService.createGrua(gruaToCreate as Grua);
    }

    saveObservable.pipe(
      catchError((error: HttpErrorResponse) => {
        console.error('GruaFormComponent: Error al guardar grúa.', error);
        this.isLoading = false;

         if (error.status === 422 && error.error && error.error.errors) {
             this.validationErrors = error.error.errors;
             console.error('Validation Errors from Backend:', this.validationErrors);
             // Mensaje general de validaci\u00F3n
             this.generalError = 'Por favor, revisa los errores en el formulario.';
         } else if (error.status === 403) {
             this.generalError = `No tienes permiso para ${this.isEditing ? 'actualizar' : 'registrar'} grúas.`;
         } else {
            // Otros tipos de errores HTTP o de red
            this.generalError = `${errorMessage} Código de error: ${error.status}`;
         }
        return of(null); // Devuelve un observable nulo para que el subscribe no entre al next()
      })
    ).subscribe(
      (response: Grua | null) => {
        this.isLoading = false;
        if (response) {
           console.log('GruaFormComponent: Grúa guardada exitosamente.', response);
           // \u00a1Mostrar el mensaje de \u00E9xito personalizado!
           this.showSuccessMessage = true;
           this.successMessageText = successMessageText;

           // La navegaci\u00F3n a la lista ocurre inmediatamente despu\u00E9s de mostrar el mensaje.
           this.cancel();
        }
      }
    );
  }

  getValidationError(fieldName: string): string | null {
    if (this.validationErrors && this.validationErrors[fieldName]) {
      return this.validationErrors[fieldName][0];
    }
    return null;
  }

  // M\u00E9todo para ocultar el mensaje de \u00E9xito manualmente
  hideSuccessMessage(): void {
    this.showSuccessMessage = false;
    this.successMessageText = '';
  }


  cancel(): void {
    console.log('GruaFormComponent: Cancelando o finalizando formulario.');
    this.router.navigate(['/gruas']);
  }

  // TODO: M\u00E9todos helper para permisos
}