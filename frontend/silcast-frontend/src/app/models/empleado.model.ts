export interface Empleado {
    id: string;
    nombreCompleto: string;
    telefono: string;
    correo: string;
    fechaIngreso: string;
    estado: 'ACTIVO' | 'INACTIVO';
    fechaNacimiento?: string;
    rfc?: string;
    nss?: string;
    salario?: number;
    antiguedad?: number;
    fechaBaja?: string;
  }