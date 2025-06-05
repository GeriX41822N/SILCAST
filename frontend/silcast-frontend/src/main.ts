import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { provideRouter } from '@angular/router';
import { routes } from './app/app.routes';
import { importProvidersFrom } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { provideHttpClient, withInterceptors } from '@angular/common/http'; // <-- Importa withInterceptors

import { authInterceptor } from './app/auth.interceptor'; // <-- Importa tu interceptor

bootstrapApplication(AppComponent, {
     providers: [
         provideRouter(routes),
         importProvidersFrom(FormsModule),
         provideHttpClient(
      // Aquí registras tus interceptores. Lista de interceptores a aplicar.
      withInterceptors([
        authInterceptor // <-- Agrega tu interceptor a la lista
        // Puedes añadir otros interceptores aquí si los tienes
      ])
    )
    ]
}).catch(err => console.error(err));
