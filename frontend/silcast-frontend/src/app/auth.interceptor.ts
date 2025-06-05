import { HttpInterceptorFn, HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { AuthService } from './auth.service'; // Importa tu AuthService
import { inject } from '@angular/core'; // Importa la función inject
import { catchError, throwError } from 'rxjs'; // Importa catchError y throwError

export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn) => {

  // Usa inject() para obtener una instancia de AuthService (solo funciona en un contexto de provider o factoría)
  // Aquí estamos en la función interceptora, que se ejecuta en un contexto adecuado.
  const authService = inject(AuthService);

  // Obtiene el token del servicio
  const token = authService.getToken();

  // Clona la petición para poder modificarla (las peticiones son inmutables)
  let clonedRequest = req;

  // Si hay un token, agrega la cabecera de autorización
  if (token) {
    clonedRequest = req.clone({
      headers: req.headers.set('Authorization', `Bearer ${token}`)
    });
    console.log('Interceptor: Token añadido a la cabecera de la petición.');
  } else {
    console.log('Interceptor: No hay token guardado. Petición enviada sin cabecera de autorización.');
  }


  // Pasa la petición clonada (con o sin cabecera) al siguiente manejador en la cadena
  // También puedes interceptar la respuesta aquí, por ejemplo, para manejar errores 401
  return next(clonedRequest).pipe(
    catchError((error) => {
      // Si recibimos un error 401 (No autorizado)
      if (error.status === 401) {
        console.error('Interceptor: Error 401 recibido.');
        // Llama al método del servicio para manejar la no autorización (ej: cerrar sesión localmente)
        authService.handleUnauthorized();
        // Puedes redirigir al login aquí si handleUnauthorized no lo hace
        // authService.logout(); // Ya se llama dentro de handleUnauthorized
        // authService.router.navigate(['/login']); // Si Router está inyectado en AuthService y lo quieres aquí
      }
      // Siempre relanza el error para que sea manejado por el subscriber original
      return throwError(() => error);
    })
  );
};
