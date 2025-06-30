@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h1 class="text-center mb-4 display-4 custom-h1-style"><i class="fas fa-shield-alt me-3"></i>Política de Privacidad</h1>
        <hr class="mb-4">

        <h2 class="h4 mt-4 mb-3">Información General</h2>
        <p class="lead text-muted">En BlueyVet S.A.C. (RUC: 20601234567), ubicada en Av. Vía de Evitamiento Norte Km. 1.2, Trujillo 13009, Perú, nos tomamos muy en serio la privacidad de tus datos y la de tus mascotas. Esta política describe cómo recopilamos, usamos y protegemos tu información.</p>

        <h2 class="h4 mt-4 mb-3">1. Información que Recopilamos</h2>
        <p>Recopilamos información que nos proporcionas directamente al registrarte, agendar citas o usar nuestros servicios. Esto puede incluir:</p>
        <ul>
            <li><strong>Datos Personales:</strong> Nombre completo, dirección de correo electrónico, número de teléfono, dirección de residencia.</li>
            <li><strong>Datos de Mascotas:</strong> Nombre de la mascota, especie, raza, fecha de nacimiento, historial médico relevante para las citas, y cualquier otra información que consideres relevante para su cuidado.</li>
            <li><strong>Información de Pagos:</strong> Detalles necesarios para procesar transacciones. Para tu seguridad, no almacenamos información sensible de tarjetas de crédito directamente en nuestros servidores; utilizamos proveedores de pago externos y seguros que cumplen con los estándares de la industria.</li>
            <li><strong>Datos de Uso:</strong> Información sobre cómo accedes y utilizas nuestra plataforma, como tu dirección IP, tipo de navegador, páginas visitadas y el tiempo que permaneces en ellas. Esto nos ayuda a mejorar nuestros servicios.</li>
        </ul>

        <h2 class="h4 mt-4 mb-3">2. Uso de tu Información</h2>
        <p>Utilizamos la información recopilada para:</p>
        <ul>
            <li>Procesar y gestionar tus citas y los servicios veterinarios para tus mascotas.</li>
            <li>Enviar recordatorios y notificaciones importantes sobre las citas, vacunas, desparasitación y otros aspectos de la salud de tus mascotas.</li>
            <li>Gestionar la compra y entrega de productos adquiridos a través de nuestra plataforma.</li>
            <li>Mejorar continuamente nuestros servicios, la funcionalidad de la plataforma y la experiencia general del usuario.</li>
            <li>Personalizar tu experiencia, ofreciéndote contenido y servicios que puedan ser de tu interés.</li>
            <li>Cumplir con obligaciones legales, reglamentarias y fiscales.</li>
            <li>Comunicarnos contigo acerca de promociones, actualizaciones de servicio o información relevante, siempre con tu consentimiento cuando sea necesario.</li>
        </ul>

        <h2 class="h4 mt-4 mb-3">3. Protección de tus Datos</h2>
        <p>Implementamos medidas de seguridad técnicas y organizativas robustas para proteger tu información personal contra accesos no autorizados, alteraciones, divulgación o destrucción. Esto incluye el cifrado de datos en tránsito y en reposo, el uso de firewalls, controles de acceso estrictos y auditorías de seguridad periódicas para asegurar la integridad y confidencialidad de tus datos.</p>

        <h2 class="h4 mt-4 mb-3">4. Compartir Información</h2>
        <p>Tu privacidad es nuestra prioridad. No vendemos, alquilamos ni comercializamos tu información personal con terceros. Podemos compartir tu información únicamente bajo las siguientes circunstancias:</p>
        <ul>
            <li><strong>Con Veterinarios y Personal de BlueyVet:</strong> Para poder brindarte eficazmente los servicios veterinarios y de atención que solicitaste.</li>
            <li><strong>Con Proveedores de Servicios Externos:</strong> Empresas que nos asisten en operaciones esenciales como procesamiento de pagos, envío de correos electrónicos, análisis de datos y soporte técnico. Estos proveedores están obligados a proteger tu información bajo acuerdos de confidencialidad estrictos y solo la utilizan para los fines que les hemos indicado.</li>
            <li><strong>Autoridades Legales:</strong> Cuando sea estrictamente requerido por ley, orden judicial o para proteger los derechos, la propiedad o la seguridad de BlueyVet, nuestros usuarios o el público.</li>
        </ul>

        <h2 class="h4 mt-4 mb-3">5. Tus Derechos y Contacto</h2>
        <p>Tienes el derecho a acceder a tu información personal, solicitar su corrección, actualización o eliminación de nuestros registros. Para ejercer cualquiera de estos derechos, o si tienes preguntas o inquietudes sobre nuestra política de privacidad, por favor contáctanos:</p>
        <ul>
            <li><strong>Correo Electrónico:</strong> <a href="mailto:blueyvet@gmail.com">blueyvet@gmail.com</a></li>
            <li><strong>Teléfono:</strong> 944280482</li>
            <li><strong>Dirección:</strong> Av. Vía de Evitamiento Norte Km. 1.2, Trujillo 13009, Perú</li>
        </ul>

        <h2 class="h4 mt-4 mb-3">6. Cambios a esta Política</h2>
        <p>Nos reservamos el derecho de actualizar esta política de privacidad periódicamente para reflejar cambios en nuestras prácticas o por motivos legales y regulatorios. Te notificaremos sobre cambios significativos mediante la publicación de la política actualizada en nuestro sitio web. Te recomendamos revisar esta política regularmente.</p>

        <p class="mt-4 text-end text-muted">Última actualización: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-h1-style {
        color: #4CAF50; /* Un verde que puede ir con BlueyVet */
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15); /* Sombra más pronunciada para un look "premium" */
        border: none; /* Elimina el borde predeterminado */
    }
    h2 {
        color: #3f51b5; /* Un azul que puede ir con BlueyVet */
        font-weight: 700; /* Más negrita */
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 8px; /* Más espacio */
        margin-top: 2.5rem !important; /* Más espacio superior */
    }
    p {
        line-height: 1.7; /* Mejor legibilidad */
        color: #555;
    }
    .lead {
        font-size: 1.15rem;
        font-weight: 400;
        color: #444;
    }
    ul {
        list-style-type: disc;
        margin-left: 25px; /* Más indentación */
        padding-left: 0;
        color: #555;
    }
    ul li {
        margin-bottom: 10px;
        line-height: 1.6;
    }
    .alert.alert-warning {
        background-color: #fff3cd; /* Amarillo suave */
        border-color: #ffeeba;
        color: #664d03;
        border-radius: 10px; /* Bordes redondeados */
        padding: 1.5rem; /* Más padding */
    }
    .alert-heading {
        color: #664d03;
        font-weight: 700;
    }
    .alert-heading i {
        color: #dc3545; /* Rojo para el icono de advertencia */
    }
    a {
        color: #3f51b5; /* Enlaces con el color azul de la cabecera */
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
@endpush