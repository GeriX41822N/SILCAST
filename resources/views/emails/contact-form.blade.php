@component('mail::message')
# Nuevo Mensaje de Contacto

¡Hola Melissa!

Has recibido un nuevo mensaje a través del formulario de contacto de tu sitio web de Silcast.

**Detalles del mensaje:**

**Nombre:** {{ $nombre }}
**Correo electrónico:** {{ $email }}
**Mensaje:**
{{ $mensaje }}

Gracias por tu atención,
El equipo de {{ config('app.name') }}
@endcomponent