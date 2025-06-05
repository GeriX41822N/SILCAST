import { Component, OnInit, OnDestroy, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Valor {
  titulo: string;
  descripcion: string;
  imagen: string;
}

interface Slide {
  titulo: string;
  texto: string;
  imagen: string;
}

// Nueva interfaz para los clientes
interface Cliente {
  nombre: string;
  imagen: string;
  url?: string; 
}

@Component({
  selector: 'app-nosotros',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './nosotros.component.html',
  styleUrls: ['./nosotros.component.scss']
})
export class NosotrosComponent implements OnInit, OnDestroy, AfterViewInit {

  @ViewChild('estadisticasSection') estadisticasSection!: ElementRef; 

  //  Estadísticas
  anioEstablecimiento = 0; 
  proyectos = 0;
  constructores = 0;
  premios = 0;

  targetStats = {
    anio: 2018, 
    proyectos: 2619,
    constructores: 870,
    premios: 86
  };

  counterInterval: any;
  private observer!: IntersectionObserver; 
  private animationStarted = false; 

  // Carrusel de valores
  valores: Valor[] = [
    {
      titulo: 'Seguridad',
      descripcion: 'Priorizamos la seguridad en cada operación para proteger a nuestro equipo y clientes.',
      imagen: '/assets/icons/seguridad.png'
    },
    {
      titulo: 'Calidad',
      descripcion: 'Comprometidos con la excelencia en cada uno de nuestros proyectos.',
      imagen: '/assets/icons/calidad.png'
    },
    {
      titulo: 'Compromiso',
      descripcion: 'Cumplimos con cada promesa y entregamos resultados sobresalientes.',
      imagen: '/assets/icons/compromiso.png'
    },
    {
      titulo: 'Innovación',
      descripcion: 'Buscamos nuevas soluciones para mejorar continuamente nuestros servicios.',
      imagen: '/assets/icons/innovacion.png'
    }
  ];
  currentValorIndex = 0;
  valorInterval: any;

  // Carrusel Misión/Visión
  misionVisionSlides: Slide[] = [
    {
      titulo: 'Misión',
      texto: 'Ser un aliado estratégico para nuestros clientes, brindando servicios confiables y de alta calidad que contribuyan al éxito de sus proyectos.',
      imagen: '/assets/images/mision.jpg'
    },
    {
      titulo: 'Visión',
      texto: 'Ser una empresa líder en soluciones industriales a nivel nacional, innovando constantemente y generando un impacto positivo en la industria.',
      imagen: '/assets/images/vision.jpg'
    }
  ];
  currentMVIndex = 0;
  mvInterval: any;

  // Clientes con Convenios 
  clientes: Cliente[] = [];

  // Mapeo de URLs para cada cliente
  private clienteUrls: { [key: string]: string } = {
    'cliente1': 'https://www.arcacontal.com/es', //arca continental
    'cliente2': 'https://www.pastelerialety.com/',   //pasteleria lety
    'cliente3': 'https://saymaq.com/', // saymaq
    'cliente4': 'https://en.donghee.co.kr/', // DONGHEE
    'cliente5': 'https://trayecto.com/tm-transportes/', //TM TRANSPORTES
    'cliente6': 'https://www.bokados.com/',//BOKADOS
    'cliente7': 'https://www.coca-cola.com/mx/es',//COCA COLA
    'cliente8': 'https://www.americanindustriesgroup.com/',//AMERICAN INDUSTRIES
    'cliente9': 'https://www.paulo.com/es/',//PAULO DATAGINNERING AT WORK
    'cliente10': 'https://gonher.com.mx/', // GONHER AUTOPARTES
    'cliente11': 'https://www.cemexmexico.com/',//CEMEX
    'cliente12': 'https://grupoenergeticos.com/language/en/home-2-2/',// ENERGEX GRUPOS ENERGETICOS
    'cliente13': 'https://www.iberdrola.com/',//IBERDROLA
    'cliente14': 'https://www.johnsoncontrols.com/',//JOHNSON CONTROLS
    'cliente0': 'https://www.arcacontal.com/es'
  };

  constructor() {
    this.clientes = this.generarClientes();
  }

  // Método para generar los clientes
  private generarClientes(): Cliente[] {
    const clientesArray: Cliente[] = [];

    for (let i = 1; i <= 14; i++) { 
      const clientKey = `cliente${i}`; 
      clientesArray.push({
        nombre: `Cliente ${i}`, 
        imagen: `/assets/logos/papelera/${clientKey}.png`,
        url: this.clienteUrls[clientKey] 
      });
    }

    if (this.clienteUrls['cliente0'] || !this.clienteUrls['cliente0']) { 
      clientesArray.unshift({ 
        nombre: 'Clientes Combinados', 
        imagen: '/assets/logos/papelera/cliente0.png',
        url: this.clienteUrls['cliente0']
      });
    }


    return clientesArray;
  }

  ngOnInit(): void {
    this.iniciarCarruseles();
  }

  ngAfterViewInit(): void {
    if (this.estadisticasSection) {
      this.setupIntersectionObserver();
    }
  }

  ngOnDestroy(): void {
    clearInterval(this.counterInterval);
    clearInterval(this.valorInterval);
    clearInterval(this.mvInterval);
    if (this.observer) {
      this.observer.disconnect();
    }
  }

  iniciarCarruseles(): void {
    this.valorInterval = setInterval(() => {
      this.currentValorIndex = (this.currentValorIndex + 1) % this.valores.length;
    }, 5000);

    this.mvInterval = setInterval(() => {
      this.currentMVIndex = (this.currentMVIndex + 1) % this.misionVisionSlides.length;
    }, 6000);
  }

  setupIntersectionObserver(): void {
    const options = {
      root: null,
      rootMargin: '0px',
      threshold: 0.5
    };

    this.observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.animationStarted) {
          this.iniciarContadorEstadisticas();
          this.animationStarted = true;
          this.observer.disconnect();
        }
      });
    }, options);

    this.observer.observe(this.estadisticasSection.nativeElement);
  }

  iniciarContadorEstadisticas(): void {
    this.anioEstablecimiento = 0;
    this.proyectos = 0;
    this.constructores = 0;
    this.premios = 0;

    let currentAnio = 0;
    let currentProyectos = 0;
    let currentConstructores = 0;
    let currentPremios = 0;

    const incrementoAnio = Math.max(1, Math.round(this.targetStats.anio / 100));
    const incrementoProyectos = Math.max(1, Math.round(this.targetStats.proyectos / 100));
    const incrementoConstructores = Math.max(1, Math.round(this.targetStats.constructores / 100));
    const incrementoPremios = Math.max(1, Math.round(this.targetStats.premios / 100));

    if (this.counterInterval) {
      clearInterval(this.counterInterval);
    }

    this.counterInterval = setInterval(() => {
      let allReached = true;

      if (currentAnio < this.targetStats.anio) {
        currentAnio += incrementoAnio;
        this.anioEstablecimiento = Math.min(currentAnio, this.targetStats.anio);
        allReached = false;
      }

      if (currentProyectos < this.targetStats.proyectos) {
        currentProyectos += incrementoProyectos;
        this.proyectos = Math.min(currentProyectos, this.targetStats.proyectos);
        allReached = false;
      }

      if (currentConstructores < this.targetStats.constructores) {
        currentConstructores += incrementoConstructores;
        this.constructores = Math.min(currentConstructores, this.targetStats.constructores);
        allReached = false;
      }

      if (currentPremios < this.targetStats.premios) {
        currentPremios += incrementoPremios;
        this.premios = Math.min(currentPremios, this.targetStats.premios);
        allReached = false;
      }

      if (allReached) {
        clearInterval(this.counterInterval);
      }
    }, 20);
  }
}