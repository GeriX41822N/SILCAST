import { Component, OnInit, OnDestroy } from '@angular/core';
import { ServiciosComponent } from '../servicios/servicios.component';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

interface Slide {
  imagen: string;
  titulo: string;
  texto: string;
}

@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [CommonModule, RouterModule, ServiciosComponent],
  templateUrl: './inicio.component.html',
  styleUrls: ['./inicio.component.scss']
})
export class InicioComponent implements OnInit, OnDestroy {
  slides: Slide[] = [
    {
      imagen: '/assets/images/inicio/portada1.jpg',
      titulo: 'Servicios integrales a la industria en México',
      texto: 'Con inteligencia y planeación, lo hacemos posible.'
    },
    {
      imagen: '/assets/images/inicio/portada2.jpg',
      titulo: 'Innovación con maquinaria de alta tecnología',
      texto: 'Maquinados y precisión garantizada para tu proyecto.'
    },
    {
      imagen: '/assets/images/inicio/portada3.jpg',//mal
      titulo: 'Grúas y equipos pesados certificados',
      texto: 'Maniobras seguras con personal altamente capacitado.'
    },
    {
      imagen: '/assets/images/inicio/portada4.jpg',
      titulo: 'Electricidad y mecánica industrial',
      texto: 'Reparamos lo imposible, con eficiencia comprobada.'
    },
    {
      imagen: '/assets/images/inicio/portada5.JPG',//mal
      titulo: 'Tu solución completa en campo',
      texto: 'Desde la planeación hasta la ejecución, SILCAST te respalda.'
    }
  ];

  currentSlide = 0;
  slideInterval: any;

  ngOnInit(): void {
    this.startAutoplay();
  }

  startAutoplay(): void {
    this.slideInterval = setInterval(() => {
      this.nextSlide();
    }, 5000);
  }

  nextSlide(): void {
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
  }

  prevSlide(): void {
    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
  }

  goToSlide(index: number): void {
    this.currentSlide = index;
  }

  ngOnDestroy(): void {
    clearInterval(this.slideInterval);
  }
}
