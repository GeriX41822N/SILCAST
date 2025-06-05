import { Component, OnInit, HostListener } from '@angular/core';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../auth.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    RouterModule,
    CommonModule
  ],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss'
})
export class HeaderComponent implements OnInit {
  mobileNavShow: boolean = false;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {

  }

  onLogout(): void {
    console.log('HeaderComponent: Logout en proceso...');
    this.authService.logout().subscribe({
      next: () => {
        console.log('HeaderComponent: Logout exitoso. Redirigiendo...');
        this.router.navigate(['/login']);
      },
      error: (err) => {
        console.error('HeaderComponent: Error al cerrar sesión.', err);
        this.router.navigate(['/login']);
      }
    });
  }

  goToLogin(): void {
    this.router.navigate(['/login']);
  }

  goToAdmin(): void {
    this.router.navigate(['/admin']);
  }

  isLoggedIn(): boolean {
    return this.authService.isLoggedIn();
  }

  toggleMobileNav(): void {
    this.mobileNavShow = !this.mobileNavShow;
    console.log(`HeaderComponent: Navegación móvil ${this.mobileNavShow ? 'activa' : 'oculta'}.`);
  }

  closeMobileNav(): void {
    this.mobileNavShow = false;
  }

  @HostListener('window:resize')
  onResize(): void {
    if (window.innerWidth >= 1280) {
      this.mobileNavShow = false;
    }
  }
}
