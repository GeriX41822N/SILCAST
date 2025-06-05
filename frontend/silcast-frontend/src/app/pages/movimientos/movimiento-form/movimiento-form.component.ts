// src/app/pages/movimientos/movimiento-form/movimiento-form.component.ts

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';

import { catchError, forkJoin, of, throwError, Observable } from 'rxjs';

import { EntradaSalidaGruaService, EntradaSalidaGrua, GruaSimple } from '../../../services/entrada-salida-grua.service';
import { EmpleadoService, EmpleadoSimple } from '../../../empleado.service';
import { GruaService } from '../../../services/grua.service';

/**
 * @class MovimientoFormComponent
 * @description Componente para el formulario de creación y edición de movimientos de grúas.
 * Permite a los usuarios registrar nuevas entradas/salidas de grúas o editar registros existentes.
 * El formulario está basado en los campos definidos en la tabla 'entradas_salidas_gruas' de la base de datos.
 */
@Component({
  selector: 'app-movimiento-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule
  ],
  templateUrl: './movimiento-form.component.html',
  styleUrls: ['./movimiento-form.component.scss']
})
export class MovimientoFormComponent implements OnInit {

  movimientoForm!: FormGroup;
  movimientoId: number | null = null;
  isEditing = false;
  isLoading = false;
  errorMessage: string | null = null;

  empleados: EmpleadoSimple[] = []; // Lista de empleados (operadores) para el dropdown.
  gruas: GruaSimple[] = []; // Lista de grúas para el dropdown.

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private movimientosService: EntradaSalidaGruaService,
    private empleadoService: EmpleadoService,
    private gruaService: GruaService
  ) { }

  ngOnInit(): void {
    this.movimientoId = this.route.snapshot.params['id'] ? +this.route.snapshot.params['id'] : null;
    this.isEditing = !!this.movimientoId;

    this.initForm();
    this.loadRelatedData();

    if (this.isEditing) {
      this.loadMovimientoData();
    }
  }

  /**
   * @method initForm
   * @description Inicializa el formulario reactivo con los controles necesarios
   * y sus respectivas validaciones. Los nombres de los controles coinciden
   * con los campos de la tabla 'entradas_salidas_gruas'.
   */
  initForm(): void {
    this.movimientoForm = this.fb.group({
      grua_id: [null, Validators.required],
      empleado_id: [null, Validators.required], // Control para el ID del empleado (operador).
      fecha_hora_entrada: [null],
      fecha_hora_salida: [null],
      destino: [null],
      kilometraje_entrada: [null],
      kilometraje_salida: [null]
    });
  }

  /**
   * @method loadRelatedData
   * @description Carga las listas de empleados (para los operadores) y grúas
   * desde sus respectivos servicios para ser mostradas en los dropdowns del formulario.
   * Utiliza forkJoin para realizar ambas peticiones en paralelo.
   */
  loadRelatedData(): void {
    this.isLoading = true;
    this.errorMessage = null;

    forkJoin({
      empleados: this.empleadoService.getEmpleadosListSimple(),
      gruas: this.gruaService.getGruasListSimple()
    }).pipe(
      catchError(error => {
        console.error('Error al cargar datos relacionados:', error);
        this.errorMessage = 'Error al cargar datos relacionados (empleados operadores, grúas).';
        this.isLoading = false;
        return of({ empleados: [], gruas: [] });
      })
    ).subscribe(results => {
      this.empleados = results.empleados;
      this.gruas = results.gruas;
      this.isLoading = false;
    });
  }

  /**
   * @method loadMovimientoData
   * @description Carga los datos de un registro de movimiento existente para el modo de edición.
   * Obtiene el registro por su ID y actualiza los valores del formulario.
   * Realiza el mapeo de 'operador_id' del backend al control 'empleado_id' del formulario.
   */
  loadMovimientoData(): void {
    if (this.movimientoId !== null) {
      this.isLoading = true;
      this.errorMessage = null;

      this.movimientosService.getMovimientoById(this.movimientoId).pipe(
        catchError(error => {
          console.error('Error al cargar datos del movimiento:', error);
          this.errorMessage = 'Error al cargar los datos del movimiento.';
          this.isLoading = false;
          return throwError(() => error);
        })
      ).subscribe((movimientoData: EntradaSalidaGrua) => {
        this.movimientoForm.patchValue({
          grua_id: movimientoData.grua_id,
          empleado_id: movimientoData.operador_id,
          fecha_hora_entrada: movimientoData.fecha_hora_entrada,
          fecha_hora_salida: movimientoData.fecha_hora_salida,
          destino: movimientoData.destino,
          kilometraje_entrada: movimientoData.kilometraje_entrada,
          kilometraje_salida: movimientoData.kilometraje_salida
        });
        this.isLoading = false;
      });
    }
  }

  /**
   * @method onSubmit
   * @description Maneja el envío del formulario. Determina si se debe crear un nuevo
   * registro o actualizar uno existente, y llama al servicio correspondiente.
   * Realiza la transformación del 'empleado_id' del formulario a 'operador_id'
   * para que coincida con la estructura esperada por el backend.
   */
  onSubmit(): void {
    this.movimientoForm.markAllAsTouched();

    if (this.movimientoForm.valid) {
      this.isLoading = true;
      this.errorMessage = null;

      const formData = this.movimientoForm.value;
      const movimientoData: EntradaSalidaGrua = {
        ...formData,
        operador_id: formData.empleado_id // Mapeo de empleado_id a operador_id para el backend.
      };

      let saveOperation: Observable<EntradaSalidaGrua>;

      if (this.isEditing && this.movimientoId !== null) {
        saveOperation = this.movimientosService.updateMovimiento(this.movimientoId, movimientoData);
      } else {
        saveOperation = this.movimientosService.createMovimiento(movimientoData);
      }

      saveOperation.pipe(
        catchError(error => {
          console.error('Error al guardar movimiento:', error);
          this.isLoading = false;
          this.errorMessage = 'Error al guardar el movimiento.';

          if (error.status === 422 && error.error && error.error.errors) {
            this.errorMessage = 'Errores de validación:';
            const backendErrors = error.error.errors;
            for (const key in backendErrors) {
              if (backendErrors.hasOwnProperty(key)) {
                this.errorMessage += ` ${key}: ${backendErrors[key].join(', ')}. `;
              }
            }
          } else if (error.error && error.error.message) {
            this.errorMessage = `Error: ${error.error.message}`;
          } else {
            this.errorMessage = 'Ocurrió un error desconocido al guardar el movimiento.';
          }

          return throwError(() => error);
        })
      ).subscribe(
        (response: EntradaSalidaGrua) => {
          this.isLoading = false;
          this.router.navigate(['/movimientos']);
        }
      );

    } else {
      console.warn('Formulario de movimiento inválido. Verifique los campos.');
      this.errorMessage = 'Por favor, complete los campos obligatorios o corrija los errores.';
    }
  }

  /**
   * @method onCancel
   * @description Redirige al usuario a la página de la lista de movimientos.
   */
  onCancel(): void {
    this.router.navigate(['/movimientos']);
  }

  /**
   * @method formControls
   * @description Getter para acceder fácilmente a los controles del formulario en la plantilla HTML.
   */
  get formControls() {
    return this.movimientoForm.controls;
  }

  /**
   * @method hasError
   * @description Verifica si un control del formulario tiene un error específico y ha sido tocado.
   * @param controlName Nombre del control a verificar.
   * @param errorName Nombre del error de validación a buscar.
   * @returns Verdadero si el control tiene el error y ha sido tocado, falso en caso contrario.
   */
  hasError(controlName: string, errorName: string): boolean | undefined {
    const control = this.movimientoForm.get(controlName);
    return control?.hasError(errorName) && control.touched;
  }
}