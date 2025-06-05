// src/app/services/grua.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

// Define la URL base de tu API de Laravel
const API_URL = 'http://localhost:8000/api'; // Aseg\u00FArate que sea correcta

// --- Interfaces de TypeScript (COMPLETADAS con campos de la tabla 'gruas') ---
// Define la estructura COMPLETA de una Gr\u00FAa
// \u00a1CORRECCI\u00d3N!: Hacer created_at y updated_at opcionales (?)
export interface Grua {
    id?: number; // Hacer ID opcional para la creaci\u00f3n
    unidad: string;
    tipo: string;
    combustible: string;
    capacidad_toneladas: number;
    pluma_telescopica_metros: number | null; // nullable
    documentacion: string | null; // nullable
    operador_id: number | null; // nullable
    precio_hora: number | null; // nullable
    ayudante_id: number | null; // nullable
    cliente_actual_id: number | null; // nullable
    estado: string;
    created_at?: string; // <--- HACER OPCIONAL
    updated_at?: string; // <--- HACER OPCIONAL

    // Puedes a\u00F1adir aqu\u00ED las relaciones si las est\u00E1s cargando en el backend (eager loading)
    // operador?: any; // O la interfaz Empleado
    // ayudante?: any; // O la interfaz Empleado
    // cliente_actual?: any; // O la interfaz Cliente
}

// Interfaz para la lista simple de gr\u00FAas (sin cambios)
export interface GruaSimple {
    id: number;
    display_name: string;
}


@Injectable({
  providedIn: 'root'
})
export class GruaService {

  private apiUrl = `${API_URL}/gruas`; // URL base para las gr\u00FAas

  constructor(private http: HttpClient) { }

  /**
   * Obtiene todas las gr\u00FAas.
   * @returns Un Observable con un array de gr\u00FAas.
   */
  getGruas(): Observable<Grua[]> {
    return this.http.get<Grua[]>(this.apiUrl);
  }

  /**
   * Obtiene una gr\u00FAa por su ID.
   * @param id El ID de la gr\u00FAa.
   * @returns Un Observable con la gr\u00FAa.
   */
  getGruaById(id: number): Observable<Grua> {
    const url = `${this.apiUrl}/${id}`;
    return this.http.get<Grua>(url);
  }

  /**
   * Crea una nueva gr\u00FAa.
   * @param grua Los datos de la gr\u00FAa a crear.
   * @returns Un Observable con la gr\u00FAa creada.
   */
  createGrua(grua: Grua): Observable<Grua> {
    // Al crear, el ID no se env\u00EDa al backend, pero el objeto en frontend puede tenerlo como opcional
    return this.http.post<Grua>(this.apiUrl, grua);
  }

  /**
   * Actualiza una gr\u00FAa existente.
   * @param id El ID de la gr\u00FAa a actualizar.
   * @param grua Los datos actualizados de la gr\u00FAa.
   * @returns Un Observable con la gr\u00FAa actualizada.
   */
  updateGrua(id: number, grua: Grua): Observable<Grua> {
    const url = `${this.apiUrl}/${id}`;
    return this.http.put<Grua>(url, grua);
  }

  /**
   * Elimina una gr\u00FAa existente.
   * @param id El ID de la gr\u00FAa a eliminar.
   * @returns Un Observable.
   */
  deleteGrua(id: number): Observable<void> {
    const url = `${this.apiUrl}/${id}`;
    return this.http.delete<void>(url);
  }

    /**
     * Obtiene una lista simple de gr\u00FAas para dropdowns.
     * @returns Un Observable con un array de objetos GruaSimple.
     */
    getGruasListSimple(): Observable<GruaSimple[]> {
        const url = `${API_URL}/gruas-list-simple`;
        return this.http.get<GruaSimple[]>(url);
    }

}