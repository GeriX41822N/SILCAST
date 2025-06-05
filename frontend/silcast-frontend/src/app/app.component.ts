import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { AuthService } from './auth.service';
import { Router } from '@angular/router'; // <-- **Importa Router aquí**

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterModule,
    HeaderComponent,
    FooterComponent,
  ],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})

export class AppComponent {

  title = 'SILCAST - Servicios integrales a la industria en México';

  constructor(
    private authService: AuthService,
    private router: Router // <-- **Inyecta Router aquí**
  ) { }

  
  // Elimina el método getProveedores() si lo tenías
}
