import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';

interface ImageData {
  url: string;
  alt: string;
}

interface GallerySection {
  key: string;
  title: string;
  description: string;
  images: ImageData[];
}

@Component({
  selector: 'app-galeria',
  standalone: true,
  imports: [
    CommonModule,
  ],
  templateUrl: './galeria.component.html',
  styleUrls: ['./galeria.component.scss']
})
export class GaleriaComponent implements OnInit, OnDestroy {

  galleryData: GallerySection[] = [];

  // Variables específicas para el carrusel de Grúas
  gruasImages: ImageData[] = [];
  currentGruasIndex = 0;
  gruasInterval: any;

  // Variables específicas para el carrusel de Servicios Industriales
  serviciosImages: ImageData[] = [];
  currentServiciosIndex = 0;
  serviciosInterval: any;

  constructor() { }

  ngOnInit(): void {
    this.loadGalleryData();
    this.iniciarCarruseles(); // Iniciar los carruseles automáticamente
  }

  ngOnDestroy(): void {
    // Limpiar los intervalos al destruir el componente para evitar fugas de memoria
    if (this.gruasInterval) {
      clearInterval(this.gruasInterval);
    }
    if (this.serviciosInterval) {
      clearInterval(this.serviciosInterval);
    }
  }

  loadGalleryData(): void {
    const data = [
      {
        key: 'gruas',
        title: 'Nuestra Flota de Grúas',
        description: 'Descubre nuestra flota de grúas de alto rendimiento para todo tipo de proyectos. Contamos con equipos modernos y personal capacitado para cada necesidad.',
        images: [
          { url: '/assets/images/gruas/imagen1.jpg', alt: 'Grúa 1' },
          { url: '/assets/images/gruas/imagen2.jpg', alt: 'Grúa 2' },
          { url: '/assets/images/gruas/imagen3.jpg', alt: 'Grúa 3' },
          { url: '/assets/images/gruas/imagen4.jpg', alt: 'Grúa 4' },
          { url: '/assets/images/gruas/imagen5.jpg', alt: 'Grúa 5' },
          { url: '/assets/images/gruas/imagen6.jpg', alt: 'Grúa 6' },
          { url: '/assets/images/gruas/imagen7.jpg', alt: 'Grúa 7' },
          { url: '/assets/images/gruas/imagen8.jpg', alt: 'Grúa 8' },
          { url: '/assets/images/gruas/imagen9.jpg', alt: 'Grúa 9' },
          { url: '/assets/images/gruas/imagen10.jpg', alt: 'Grúa 10' },
          { url: '/assets/images/gruas/imagen11.jpg', alt: 'Grúa 11' },
          { url: '/assets/images/gruas/imagen12.jpg', alt: 'Grúa 12' },
          { url: '/assets/images/gruas/imagen13.jpg', alt: 'Grúa 13' }
        ]
      },
      {
        key: 'servicios-industriales',
        title: 'Servicios Industriales Especializados',
        description: 'Ofrecemos una amplia gama de servicios industriales especializados. Desde mantenimiento de equipos hasta soluciones integrales para tus operaciones, garantizamos eficiencia y seguridad.',
        images: [
          { url: '/assets/images/industriales/imagen1.jpg', alt: 'Servicio Industrial 1' },
          { url: '/assets/images/industriales/imagen2.jpg', alt: 'Servicio Industrial 2' },
          { url: '/assets/images/industriales/imagen3.jpg', alt: 'Servicio Industrial 3' },
          { url: '/assets/images/industriales/imagen4.jpg', alt: 'Servicio Industrial 4' },
          { url: '/assets/images/industriales/imagen5.jpg', alt: 'Servicio Industrial 5' },
          { url: '/assets/images/industriales/imagen6.jpg', alt: 'Servicio Industrial 6' },
          { url: '/assets/images/industriales/imagen7.jpg', alt: 'Servicio Industrial 7' },
          { url: '/assets/images/industriales/imagen8.jpg', alt: 'Servicio Industrial 8' },
          { url: '/assets/images/industriales/imagen9.jpg', alt: 'Servicio Industrial 9' },
          { url: '/assets/images/industriales/imagen10.jpg', alt: 'Servicio Industrial 10' },
          { url: '/assets/images/industriales/imagen11.jpg', alt: 'Servicio Industrial 11' },
          { url: '/assets/images/industriales/imagen12.jpg', alt: 'Servicio Industrial 12' },
          { url: '/assets/images/industriales/imagen13.jpg', alt: 'Servicio Industrial 13' }
        ]
      }
    ];

    this.galleryData = data;
    this.gruasImages = data.find(s => s.key === 'gruas')?.images || [];
    this.serviciosImages = data.find(s => s.key === 'servicios-industriales')?.images || [];
  }

  iniciarCarruseles(): void {
    if (this.gruasImages.length > 0) {
      this.gruasInterval = setInterval(() => {
        this.nextGruasImage();
      }, 4000);
    }

    if (this.serviciosImages.length > 0) {
      this.serviciosInterval = setInterval(() => {
        this.nextServiciosImage();
      }, 5000);
    }
  }

  nextGruasImage(): void {
    if (this.gruasImages.length === 0) return;
    this.currentGruasIndex = (this.currentGruasIndex + 1) % this.gruasImages.length;
  }

  prevGruasImage(): void {
    if (this.gruasImages.length === 0) return;
    this.currentGruasIndex = (this.currentGruasIndex - 1 + this.gruasImages.length) % this.gruasImages.length;
  }

  nextServiciosImage(): void {
    if (this.serviciosImages.length === 0) return;
    this.currentServiciosIndex = (this.currentServiciosIndex + 1) % this.serviciosImages.length;
  }

  prevServiciosImage(): void {
    if (this.serviciosImages.length === 0) return;
    this.currentServiciosIndex = (this.currentServiciosIndex - 1 + this.serviciosImages.length) % this.serviciosImages.length;
  }

  // Getters para las imágenes adyacentes del carrusel de Servicios Industriales
  // Retornan 'ImageData' directamente porque sabemos que el array no estará vacío
  get currentServiciosPrevImage(): ImageData {
    const prevIndex = (this.currentServiciosIndex - 1 + this.serviciosImages.length) % this.serviciosImages.length;
    return this.serviciosImages[prevIndex];
  }

  get currentServiciosNextImage(): ImageData {
    const nextIndex = (this.currentServiciosIndex + 1) % this.serviciosImages.length;
    return this.serviciosImages[nextIndex];
  }
}