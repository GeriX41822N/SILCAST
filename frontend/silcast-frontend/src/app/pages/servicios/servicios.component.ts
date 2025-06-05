import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Servicio {
  titulo: string;
  descripcion: string;
  imagen: string;
  detalles: string[];
}

@Component({
  selector: 'app-servicios',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './servicios.component.html',
  styleUrls: ['./servicios.component.scss']
})
export class ServiciosComponent implements OnInit, OnDestroy {
  servicios: Servicio[] = [
    {
      titulo: 'Soldadura Especializada',
      descripcion: 'Soluciones de alta precisión y calidad en soldadura para la industria.',
      imagen: '/assets/images/servicios/soldadura.jpg',
      detalles: [
        'Soldadura TIG: ideal para trabajos finos y acabados limpios.',
        'Soldadura MIG: rápida y eficiente para producción continua.',
        'Soldadura por arco eléctrico: confiable para estructuras pesadas y exteriores.'
      ]
    },
    {
      titulo: 'Maquinados Industriales',
      descripcion: 'Procesos de maquinado con tecnología avanzada y máxima eficiencia.',
      imagen: '/assets/images/servicios/maquinados.jpg',
      detalles: [
        'Tornos CNC y convencionales para piezas de precisión.',
        'Fresado de componentes metálicos y plásticos.',
        'Corte, barrenado, roscado y rectificado de alta precisión.',
        'Fabricación de partes bajo plano técnico.'
      ]
    },
    {
      titulo: 'Servicios Electromecánicos',
      descripcion: 'Mantenimiento, instalación y reparación de sistemas electromecánicos industriales.',
      imagen: '/assets/images/servicios/electromecanicos.jpg',
      detalles: [
        'Mantenimiento a motores eléctricos, tableros y contactores.',
        'Instalación de cableado y canalización industrial.',
        'Automatización con sensores, PLCs y variadores de frecuencia.',
        'Diagnóstico y reparación de fallas eléctricas y mecánicas.'
      ]
    },
    {
      titulo: 'Maniobras e Izajes con Grúas',
      descripcion: 'Movemos lo imposible con seguridad y precisión.',
      imagen: '/assets/images/servicios/maniobras.jpg',
      detalles: [
        'Grúa 50 toneladas - Unidad 23 (Pluma 29.3 m)',
        'Grúa 30 toneladas - Unidad 25 (Pluma 30.8 m)',
        'Grúa 28 toneladas - Unidad 24 (Pluma 28 m)',
        'Genie GTH1056 - 4.5 t / Pluma 17.32 m',
        'Montacargas CAT - 15 toneladas, ideal para patios industriales'
      ]
    }
  ];

  slideActivo: number = 0;
  intervaloAuto!: any;

  ngOnInit(): void {
    this.iniciarCarrusel();
  }

  ngOnDestroy(): void {
    clearInterval(this.intervaloAuto);
  }

  iniciarCarrusel(): void {
    this.intervaloAuto = setInterval(() => {
      this.siguiente();
    }, 6000); // cada 6 segundos cambia
  }

  siguiente(): void {
    this.slideActivo = (this.slideActivo + 1) % this.servicios.length;
  }

  anterior(): void {
    this.slideActivo = (this.slideActivo - 1 + this.servicios.length) % this.servicios.length;
  }

  setSlide(index: number): void {
    this.slideActivo = index;
    // Reiniciar temporizador si se usa flecha manual o clic directo
    clearInterval(this.intervaloAuto);
    this.iniciarCarrusel();
  }

  isActivo(index: number): boolean {
    return index === this.slideActivo;
  }
}
