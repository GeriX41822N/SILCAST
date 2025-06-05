import { Routes } from '@angular/router';

// ---------------- COMPONENTES PÚBLICOS ----------------
import { InicioComponent } from './pages/inicio/inicio.component';
import { NosotrosComponent } from './pages/nosotros/nosotros.component';
import { ServiciosComponent } from './pages/servicios/servicios.component';
import { GaleriaComponent } from './pages/galeria/galeria.component';
import { ContactoComponent } from './pages/contacto/contacto.component';
import { LoginComponent } from './pages/login/login.component';

// ---------------- COMPONENTES ADMIN ----------------
import { AdminComponent } from './pages/admin/admin.component';
import { EmpleadosListComponent } from './pages/empleados/empleados-list/empleados-list.component';
import { ProveedoresComponent } from './pages/proveedores/proveedores.component';
import { UserManagementComponent } from './pages/user-management/user-management.component';
import { MovimientosListComponent } from './pages/movimientos/movimientos-list/movimientos-list.component';
import { GruaListComponent } from './pages/gruas/grua-list/grua-list.component';
import { GruaFormComponent } from './pages/gruas/grua-form/grua-form.component';
// *** Importar el MovimientoFormComponent ***
import { MovimientoFormComponent } from './pages/movimientos/movimiento-form/movimiento-form.component';


// ---------------- GUARD ----------------
import { authGuard } from './auth.guard';

export const routes: Routes = [
    // --------- RUTAS PÚBLICAS ---------
  { path: '', component: InicioComponent },
  { path: 'nosotros', component: NosotrosComponent },
  { path: 'servicios', component: ServiciosComponent },
  { path: 'galeria', component: GaleriaComponent },
  { path: 'contacto', component: ContactoComponent },
  { path: 'login', component: LoginComponent },

  // --------- RUTA DE DASHBOARD ADMIN (Protegida) ---------
  {
    path: 'admin',
    component: AdminComponent,
    canActivate: [authGuard]
  },

  // --------- SECCIONES ADMINISTRABLES (Protegidas) ---------
  {
    path: 'empleados',
    component: EmpleadosListComponent,
    canActivate: [authGuard]
  },
  {
    path: 'proveedores',
    component: ProveedoresComponent,
    canActivate: [authGuard]
  },
  {
    path: 'user-management',
    component: UserManagementComponent,
    canActivate: [authGuard]
  },
  {
    path: 'movimientos',
    component: MovimientosListComponent,
    canActivate: [authGuard]
  },
  // *** Rutas para el formulario de Movimientos ***
  {
    path: 'movimientos/new',
    component: MovimientoFormComponent,
    canActivate: [authGuard]
  },
  {
    path: 'movimientos/edit/:id',
    component: MovimientoFormComponent,
    canActivate: [authGuard]
  },

  // Rutas existentes para Gruas
  {
    path: 'gruas',
    component: GruaListComponent,
    canActivate: [authGuard]
  },
  {
    path: 'gruas/new',
    component: GruaFormComponent,
    canActivate: [authGuard]
  },
  {
    path: 'gruas/edit/:id',
    component: GruaFormComponent,
    canActivate: [authGuard]
  },

  // --------- RUTA CATCH-ALL ---------
  { path: '**', redirectTo: '', pathMatch: 'full' }
];