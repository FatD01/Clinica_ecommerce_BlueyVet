<x-mail::message>
# Nuevo Mensaje de Contacto

Has recibido un nuevo mensaje a través del formulario de contacto de BlueyVet.

**Nombre:** {{ $contactMessage->name }} <br>
**Email:** <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a> <br>
**Asunto:** {{ $contactMessage->subject ?? 'N/A' }}

**Mensaje:**
{{ $contactMessage->message }}

Para responder, puedes hacerlo directamente a {{ $contactMessage->email }}.

Gracias,<br>
El equipo de BlueyVet
</x-mail::message>