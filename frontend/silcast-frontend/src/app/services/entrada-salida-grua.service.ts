// src/app/services/entrada-salida-grua.service.ts

import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

// Define la URL base de tu API de Laravel
const API_URL = 'http://localhost:8000/api';

// --- Definiciones de Interfaces de TypeScript ---
// Reflejan la estructura de datos esperada del backend, incluyendo relaciones eager loaded.

/**
 * @interface Grua
 * @description Define la estructura de datos para un objeto Grúa tal como se recibe en las relaciones eager loaded.
 * Refleja las propiedades de la tabla 'gruas' devueltas por el backend en el contexto de un movimiento.
 */
export interface Grua {
    id: number;
    unidad: string; // Usar 'unidad' según los logs del backend
    tipo: string; // Usar 'tipo' según los logs del backend
    // Otros campos relevantes de la tabla 'gruas' si se devuelven en la relación eager loaded.
}

/**
 * @interface GruaSimple
 * @description Define la estructura de datos para un objeto Grúa utilizado en listas o selectores.
 * Contiene solo los campos necesarios para mostrar en un dropdown, por ejemplo.
 */
export interface GruaSimple {
    id: number;
    display_name: string; // Campo usado para mostrar en la lista/selector
}


/**
 * @interface Empleado
 * @description Define la estructura de datos para un objeto Empleado (Operador),
 * utilizada en relaciones. Refleja las propiedades esenciales devueltas por la relación 'operador'.
 */
export interface Empleado {
    id: number;
    nombre: string;
    apellido_paterno: string;
    apellido_materno?: string | null; // Puede ser nulo
    // Otros campos relevantes si la relación 'operador' los devuelve (ej. numero_empleado).
}

// La interfaz Cliente y sus referencias se eliminan al no estar en la tabla de movimientos

/**
 * @interface EntradaSalidaGrua
 * @description Define la estructura de un registro de movimiento de grúa
 * tal como se espera del backend, basándose estrictamente en los campos de la tabla de BD mostrada.
 * Incluye propiedades para relaciones eager loaded.
 */
export interface EntradaSalidaGrua {
    id?: number; // Opcional al crear, presente al recibir/actualizar
    grua_id: number; // Clave foránea a 'gruas'
    operador_id: number | null; // Clave foránea a 'empleados', puede ser nulo. Usado para guardar/cargar desde BD.

    fecha_hora_entrada: string | null; // Formato string, ej. 'YYYY-MM-DD HH:mm:ss' del backend
    fecha_hora_salida: string | null; // Puede ser nulo si es solo una entrada

    destino: string | null; // Campo de ubicación en la BD ('destino')

    kilometraje_entrada: number | null;
    kilometraje_salida: number | null; // Puede ser nulo

    created_at?: string; // Timestamps de Laravel
    updated_at?: string; // Timestamps de Laravel

    // --- Relaciones Eager Loaded ---
    grua?: Grua; // Objeto de la Grúa relacionada
    operador?: Empleado; // Objeto del Empleado (Operador) relacionado
    // La relación 'cliente' se elimina al no estar en la tabla de movimientos
}


/**
 * @class EntradaSalidaGruaService
 * @description Servicio para interactuar con los endpoints de la API de movimientos de grúas.
 * Proporciona métodos para operaciones CRUD y filtrado, basándose en los campos de la tabla de BD mostrada.
 */
@Injectable({
    providedIn: 'root'
})
export class EntradaSalidaGruaService {

    // URL base para los endpoints de movimientos. AJUSTA SEGÚN TU ENTORNO.
    private apiUrl = `${API_URL}/movimientos-grua`;

    constructor(private http: HttpClient) { }

    /**
     * @method getMovimientos
     * @description Obtiene una lista completa de todos los registros de movimientos.
     * Corresponde al método index en el controlador.
     * @returns {Observable<EntradaSalidaGrua[]>} Observable con un array de registros de movimientos.
     */
    getMovimientos(): Observable<EntradaSalidaGrua[]> {
        return this.http.get<EntradaSalidaGrua[]>(this.apiUrl);
    }

    /**
     * @method getMovimientoById
     * @description Obtiene los detalles de un registro de movimiento específico por su ID.
     * Corresponde al método show en el controlador.
     * @param {number} id El ID del registro de movimiento.
     * @returns {Observable<EntradaSalidaGrua>} Observable con el registro de movimiento.
     */
    getMovimientoById(id: number): Observable<EntradaSalidaGrua> {
        const url = `${this.apiUrl}/${id}`;
        return this.http.get<EntradaSalidaGrua>(url);
    }

    /**
     * @method createMovimiento
     * @description Crea un nuevo registro de movimiento.
     * Corresponde al método store en el controlador.
     * Construye el payload con los campos de la base de datos.
     * @param {EntradaSalidaGrua} movimiento Los datos a crear.
     * @returns {Observable<EntradaSalidaGrua>} Observable con el registro creado.
     */
    createMovimiento(movimiento: EntradaSalidaGrua): Observable<EntradaSalidaGrua> {
        // Construye el payload para enviar al backend, basado en los campos de la BD.
        const payload = {
            grua_id: movimiento.grua_id,
            operador_id: movimiento.operador_id, // Usar directamente operador_id
            fecha_hora_entrada: movimiento.fecha_hora_entrada,
            fecha_hora_salida: movimiento.fecha_hora_salida,
            destino: movimiento.destino, // Usar directamente destino
            kilometraje_entrada: movimiento.kilometraje_entrada,
            kilometraje_salida: movimiento.kilometraje_salida,
            // No enviar otros campos que no están en la tabla de BD mostrada
        };

        return this.http.post<EntradaSalidaGrua>(this.apiUrl, payload);
    }

    /**
     * @method updateMovimiento
     * @description Actualiza un registro de movimiento existente.
     * Corresponde al método update en el controlador.
     * Construye el payload con los campos de la base de datos.
     * @param {number} id El ID del registro a actualizar.
     * @param {EntradaSalidaGrua} movimiento Los datos actualizados.
     * @returns {Observable<EntradaSalidaGrua>} Observable con el registro actualizado.
     */
    updateMovimiento(id: number, movimiento: EntradaSalidaGrua): Observable<EntradaSalidaGrua> {
        const url = `${this.apiUrl}/${id}`;
         // Construye el payload para enviar al backend, basado en los campos de la BD.
         const payload = {
            grua_id: movimiento.grua_id,
            operador_id: movimiento.operador_id, // Usar directamente operador_id
            fecha_hora_entrada: movimiento.fecha_hora_entrada,
            fecha_hora_salida: movimiento.fecha_hora_salida,
            destino: movimiento.destino, // Usar directamente destino
            kilometraje_entrada: movimiento.kilometraje_entrada,
            kilometraje_salida: movimiento.kilometraje_salida,
            // No enviar otros campos que no están en la tabla de BD mostrada
        };

        return this.http.put<EntradaSalidaGrua>(url, payload);
    }

    /**
     * @method deleteMovimiento
     * @description Elimina un registro de movimiento existente por su ID.
     * Corresponde al método destroy en el controlador.
     * @param {number} id El ID del registro a eliminar.
     * @returns {Observable<void>} Observable vacío.
     */
    deleteMovimiento(id: number): Observable<void> {
        const url = `${this.apiUrl}/${id}`;
        return this.http.delete<void>(url);
    }

    /**
     * @method filterMovimientosByDate
     * @description Obtiene registros de movimientos filtrados por rango de fechas y/o grúa.
     * Mapea al endpoint filter-by-date en el controlador.
     * @param {string | null} startDate Fecha de inicio del rango (YYYY-MM-DD). Opcional.
     * @param {string | null} endDate Fecha de fin del rango (YYYY-MM-DD). Opcional.
     * @param {number | null} gruaId ID de la grúa para filtrar. Opcional.
     * @returns {Observable<EntradaSalidaGrua[]>} Observable con un array de registros de movimientos filtrados.
     */
    filterMovimientosByDate(startDate?: string | null, endDate?: string | null, gruaId?: number | null): Observable<EntradaSalidaGrua[]> {
        let params = new HttpParams();
        if (startDate) {
            params = params.set('start_date', startDate);
        }
        if (endDate) {
            params = params.set('end_date', endDate);
        }
        if (gruaId !== null && gruaId !== undefined) {
            params = params.set('grua_id', gruaId.toString());
        }
        const url = `${this.apiUrl}/filter-by-date`;
        // Asumiendo que el backend filter-by-date también carga las relaciones 'grua' y 'operador'
        return this.http.get<EntradaSalidaGrua[]>(url, { params });
    }

    /**
     * @method getMovimientosByGrua
     * @description Obtiene los registros de movimientos de una grúa específica por su ID.
     * Mapea al endpoint /by-grua/{gruaId} en el backend.
     * @param {number} gruaId El ID de la grúa.
     * @returns {Observable<EntradaSalidaGrua[]>} Observable con un array de registros de movimientos de la grúa.
     */
    getMovimientosByGrua(gruaId: number): Observable<EntradaSalidaGrua[]> {
        const url = `${this.apiUrl}/by-grua/${gruaId}`;
         // Asumiendo que el backend /by-grua/{gruaId} también carga las relaciones 'grua' y 'operador'
        return this.http.get<EntradaSalidaGrua[]>(url);
    }
}