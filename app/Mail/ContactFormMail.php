<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    // Propiedades públicas que se pasarán a la vista del correo
    public $nombre;
    public $email;
    public $mensaje;

    /**
     * Crea una nueva instancia de mensaje.
     */
    public function __construct($nombre, $email, $mensaje)
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->mensaje = $mensaje;
    }

    /**
     * Obtiene el sobre del mensaje (remitente, asunto).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // Opcional: El 'from' aquí anula el MAIL_FROM_ADDRESS del .env si lo necesitas.
            // Idealmente, el remitente debe ser una dirección de tu dominio para evitar spam.
            // Si quieres que el "responder a" sea el correo del cliente, puedes añadir un 'replyTo'
            replyTo: [$this->email], // Esto hace que al responder al correo, respondas al cliente
            subject: 'Nuevo Mensaje de Contacto desde tu Sitio Web',
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            // Esto indica que el contenido del correo se renderizará usando una vista Blade Markdown.
            // Laravel tiene plantillas de correo muy bonitas por defecto para esto.
            markdown: 'emails.contact-form',
        );
    }

    /**
     * Obtiene los adjuntos para el mensaje.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return []; // No necesitamos adjuntos para un formulario de contacto simple
    }
}