import { CanActivateFn, Router, UrlTree } from '@angular/router';
import { AuthService } from './auth.service'; 
import { inject } from '@angular/core';
import { Observable } from 'rxjs'; // 

// CanActivateFn es el tipo para guards funcionales standalone
export const authGuard: CanActivateFn = (route, state) => {

  // Usa inject() para obtener instancias de AuthService y Router dentro del guard
  const authService = inject(AuthService);
  const router = inject(Router);

  console.log('AuthGuard: Evaluando acceso a la ruta:', state.url);

  // Verifica si el usuario está logeado usando el AuthService
  if (authService.isLoggedIn()) {
    console.log('AuthGuard: Usuario autenticado. Permitiendo acceso.');
    return true; // Permite la navegación si el usuario está logeado
  } else {
    console.log('AuthGuard: Usuario NO autenticado. Redirigiendo al login.');
    // Si el usuario no está logeado, redirige a la página de login
    // Devuelve un UrlTree para indicar a Angular que redirija
    return router.createUrlTree(['/login']); // <-- Redirige a la ruta '/login'
  }
};
