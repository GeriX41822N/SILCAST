import { Component, ViewChild } from '@angular/core'; // Importa ViewChild
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms'; // Importa NgForm
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-contacto',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule],
  templateUrl: './contacto.component.html',
  styleUrl: './contacto.component.scss'
})
export class ContactoComponent {
  // @ViewChild nos permite acceder al formulario desde la plantilla
  @ViewChild('contactForm') contactForm!: NgForm;

  nombre: string = '';
  email: string = '';
  mensaje: string = '';
  mostrarModal: boolean = false;
  mensajeModal: string = '';

  constructor(private http: HttpClient) { }

  // Recibimos el formulario como argumento
  enviarMensaje(form: NgForm) { // Cambia el método para aceptar NgForm
    // Si por alguna razón se intenta enviar un formulario inválido (el botón está deshabilitado, pero es una buena práctica)
    if (form.invalid) {
      console.warn('Intento de envío de formulario inválido.');
      this.mensajeModal = "Por favor, complete todos los campos obligatorios y corrija los errores.";
      this.mostrarModal = true;
      return; // Detiene la ejecución si el formulario no es válido
    }

    const datosFormulario = {
      nombre: this.nombre,
      email: this.email,
      mensaje: this.mensaje
    };

    const urlBackend = 'http://localhost:8000/api/enviar-correo'; 
    
    this.http.post(urlBackend, datosFormulario)
      .subscribe({
        next: (response) => {
          console.log('Correo enviado con éxito', response);
          this.mensajeModal = "¡Su correo ha sido enviado a uno de nuestros administrativos de Silcast! Espera su respuesta por medio del correo que nos ha contactado.";
          this.mostrarModal = true;

          // ¡IMPORTANTE! Resetear el formulario completamente después del éxito
          form.resetForm(); // Esto limpia los campos y el estado de validación
        },
        error: (error) => {
          console.error('Error al enviar el correo', error);
          this.mensajeModal = "Hubo un error al enviar su mensaje. Por favor, intente de nuevo más tarde.";
          this.mostrarModal = true;
          // No limpiamos el formulario en caso de error para que el usuario pueda corregir
        }
      });
  }

  cerrarModal() {
    this.mostrarModal = false;
  }
}