import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../auth.service';

// Interfaz para los elementos de navegación
interface NavItem {
  label: string;      // Texto del botón
  path: string;       // Ruta a la que redirige
  permission: string; // Permiso necesario (de Spatie)
  icon: string;       // Nombre del ícono (Material Icons)
}

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule
  ],
  templateUrl: './admin.component.html',
  styleUrl: './admin.component.scss'
})
export class AdminComponent implements OnInit {

  // Ítems de navegación basados en los permisos del backend
  navItems: NavItem[] = [
    { label: 'Gestión de Empleados', path: '/empleados', permission: 'view employees', icon: 'people' },
    { label: 'Gestión de Proveedores', path: '/proveedores', permission: 'view suppliers', icon: 'business' },
    { label: 'Gestión de Roles', path: '/user-management', permission: 'manage users', icon: 'admin_panel_settings' },
    { label: 'Gestión de Movimientos', path: '/movimientos', permission: 'view movements', icon: 'sync_alt' },
    { label: 'Gestión de Grúas', path: '/gruas', permission: 'view cranes', icon: 'engineering' }, // Cambiado a 'engineering'
  ];

  // Bandera para saber si hay ítems visibles según permisos
  hasVisibleNavItems: boolean = false;

  // Inyectar AuthService
  constructor(public authService: AuthService) {}

  ngOnInit(): void {
    this.updateVisibleNavItemsStatus();
  }

  // Filtra los ítems según los permisos del usuario
  updateVisibleNavItemsStatus(): void {
    const visibleItems = this.navItems.filter(item => this.authService.hasPermission(item.permission));
    this.hasVisibleNavItems = visibleItems.length > 0;

    console.log('AdminComponent: Ítems de navegación visibles:', visibleItems);
    console.log('AdminComponent: ¿Hay ítems visibles?', this.hasVisibleNavItems);
  }

  // Método para cerrar sesión
  logout(): void {
    console.log('AdminComponent: Cerrando sesión...');
    this.authService.logout().subscribe({
      next: () => {
        console.log('AdminComponent: Logout exitoso.');
        this.authService.handleUnauthorized(); // Limpia datos y redirige a /login
      },
      error: (err) => {
        console.error('AdminComponent: Error durante el logout:', err);
        this.authService.handleUnauthorized(); // Aún con error, limpiar y redirigir
      }
    });
  }
}