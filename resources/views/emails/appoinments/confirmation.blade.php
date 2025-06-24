@component('mail::message')
# ¡Tu Cita en BlueyVet y Pago Confirmados!

Hola **{{ $user->name }}**,

¡Nos complace confirmarte que tu cita ha sido **agendada exitosamente** y tu **pago ha sido procesado**!

---

## Detalles de tu Cita:

* **Servicio:** **{{ $service->name }}**
* **Mascota:** **{{ $mascota->name }}**
* **Veterinario(a):** **{{ $veterinarian->name }}**
* **Fecha:** **{{ $appointment_date }}**
* **Hora:** **{{ $appointment_time }}**

---

## Detalles de tu Pago:

* **Monto Pagado:** **{{ number_format($price, 2) }} {{ config('app.locale_currency', 'PEN') }}**
* **Método de Pago:** **{{ $payment_method }}**

---

## Adjuntos:

Hemos adjuntado a este correo tu **comprobante de cita** y el **recibo de pago** para tu referencia. Por favor, revísalos.

Puedes ver y gestionar tus citas desde tu panel de usuario en cualquier momento:

@component('mail::button', ['url' => route('client.citas.index')])
Ver Mis Citas
@endcomponent

Si tienes alguna pregunta o necesitas reagendar tu cita, por favor no dudes en contactarnos.

Gracias por confiar en BlueyVet. ¡Esperamos verte pronto!

Saludos cordiales,

El Equipo de BlueyVet
@endcomponent