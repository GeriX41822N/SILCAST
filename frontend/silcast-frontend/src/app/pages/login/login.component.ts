// src/app/pages/login/login.component.ts
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../auth.service';
import { Router, ActivatedRoute } from '@angular/router';
import { CommonModule } from '@angular/common';

import { catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';


@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    FormsModule,
    CommonModule
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent implements OnInit {

  email: string = '';
  password: string = '';
  errorMessage: string | null = null; // Podemos seguir usándolo para otros tipos de errores si quieres, o eliminarlo
  isLoading: boolean = false;

  // Propiedades para el modal de acceso restringido
  showRestrictedAccessModal: boolean = false;
  restrictedAccessMessage: string = '';

  constructor(
    private authService: AuthService,
    private router: Router,
    private activatedRoute: ActivatedRoute
  ) { }

  ngOnInit(): void {
    // Lógica para cuando se redirige a login desde una ruta protegida
    this.activatedRoute.queryParams.subscribe(params => {
      if (params['status'] === 'unauthorized') {
        this.restrictedAccessMessage = 'El sistema de acceso está reservado exclusivamente para el personal autorizado de Silcast. Por favor, contacta a tu administrador si crees que esto es un error.';
        this.showRestrictedAccessModal = true;

        setTimeout(() => {
          this.router.navigate(['/']);
          this.showRestrictedAccessModal = false;
        }, 5000); // 5 segundos
      }
    });
  }

  onLogin() {
    this.errorMessage = null; // Limpiamos mensajes de error anteriores
    this.isLoading = true;

    console.log('Intentando iniciar sesión desde el componente de Login...');
    console.log('Email:', this.email);

    this.authService.login(this.email, this.password).pipe(
        catchError((error: HttpErrorResponse) => {
          console.error('Error en el login desde LoginComponent:', error);
          this.isLoading = false;

          // --- MODIFICACIÓN CLAVE AQUÍ ---
          // Si el login falla (especialmente por 401 Unauthorized o credenciales inválidas)
          // mostramos el modal de acceso restringido.
          if (error.status === 401 || (error.status === 422 && error.error && error.error.errors)) {
            this.restrictedAccessMessage = 'Acceso no autorizado: El sistema de acceso está reservado exclusivamente para el personal autorizado de Silcast. Si eres parte del personal, verifica tus credenciales o contacta a tu administrador.';
            this.showRestrictedAccessModal = true;

            // Opcional: Podrías redirigir al inicio después de un tiempo, o dejar que el usuario cierre el modal.
            // Para este caso, vamos a dejar que el usuario cierre el modal para que pueda leerlo.
            // Si quieres redirigir automáticamente, descomenta el siguiente bloque:
            /*
            setTimeout(() => {
              this.router.navigate(['/']);
              this.showRestrictedAccessModal = false;
            }, 5000); // 5 segundos para leer el mensaje
            */

          } else {
            // Para cualquier otro tipo de error no esperado, mostramos un mensaje genérico.
            this.errorMessage = 'Ocurrió un error inesperado al intentar iniciar sesión. Por favor, inténtalo más tarde.';
          }
          return of(null);
        })
    ).subscribe(response => {
        if (response) { // Solo si la respuesta no es null (es decir, el login fue exitoso)
            console.log('Login exitoso desde LoginComponent', response);
            console.log('Login exitoso. Redirigiendo a /admin...');
            this.router.navigate(['/admin']);
            // Reiniciar el formulario después de un login exitoso
            this.email = '';
            this.password = '';
        }
        this.isLoading = false;
    });
  }

  // Nuevo método para cerrar el modal de acceso restringido manualmente
  closeRestrictedAccessModal() {
    this.showRestrictedAccessModal = false;
    // Decidir si redirigir al inicio al cerrar manualmente, o si se queda en la página de login.
    // Si quieres que al cerrar el modal siempre vaya al inicio, descomenta la línea de abajo.
    this.router.navigate(['/']); // Redirige al inicio al cerrar el modal
  }
}