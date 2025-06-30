@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h1 class="text-center mb-4 display-4 custom-h1-style"><i class="fas fa-file-contract me-3"></i>Términos de Servicio</h1>
        <hr class="mb-4">

        <h2 class="h4 mt-4 mb-3">Información General</h2>
        <p class="lead text-muted">Bienvenido a BlueyVet S.A.C. (RUC: 20601234567), ubicada en Av. Vía de Evitamiento Norte Km. 1.2, Trujillo 13009, Perú. Al usar nuestra plataforma, aceptas los siguientes términos y condiciones. Por favor, léelos cuidadosamente.</p>

        <h2 class="h4 mt-4 mb-3">1. Aceptación de los Términos</h2>
        <p>Al acceder o utilizar cualquier parte de los servicios de BlueyVet (el "Servicio"), aceptas quedar vinculado por estos Términos de Servicio. Si no estás de acuerdo con todos los términos y condiciones de este acuerdo, no podrás acceder a la página web ni utilizar ningún servicio. Nos reservamos el derecho de actualizar, cambiar o reemplazar cualquier parte de estos Términos de Servicio mediante la publicación de actualizaciones y/o cambios en nuestro sitio web.</p>

        <h2 class="h4 mt-4 mb-3">2. Descripción del Servicio</h2>
        <p>BlueyVet es una plataforma digital diseñada para facilitar la gestión integral del cuidado de tus mascotas, incluyendo el agendamiento y administración de citas veterinarias, envío de recordatorios de salud (como vacunación y desparasitación), gestión de historiales de mascotas y la venta de productos relacionados con el bienestar animal. Nuestro objetivo es conectar a los dueños de mascotas con profesionales veterinarios y productos de calidad de manera eficiente y segura.</p>

        <h2 class="h4 mt-4 mb-3">3. Registro de Cuenta y Seguridad</h2>
        <p>Para acceder a ciertas funciones del Servicio, deberás registrarte y crear una cuenta de usuario. Te comprometes a proporcionar información precisa, completa y actualizada durante el proceso de registro y a mantenerla así. Eres el único responsable de mantener la confidencialidad de tu contraseña y de todas las actividades que ocurran bajo tu cuenta. Cualquier uso no autorizado de tu cuenta debe ser notificado de inmediato a BlueyVet.</p>

        <h2 class="h4 mt-4 mb-3">4. Pagos y Políticas de Citas y Productos</h2>
        <p>Algunos servicios y la compra de productos en BlueyVet pueden requerir un pago. Te comprometes a pagar todas las tarifas aplicables y los impuestos asociados con tu uso de nuestros servicios y la adquisición de productos. Todas las transacciones de pago son finales y están sujetas a las siguientes políticas:</p>

        <div class="alert alert-warning mt-3" role="alert">
            <h4 class="alert-heading h5"><i class="fas fa-exclamation-triangle me-2"></i>Política de Reprogramación y Devoluciones</h4>
            <p class="mb-2"><strong>1. Para Citas:</strong></p>
            <ul>
                <li>Las citas agendadas y pagadas a través de BlueyVet no son reembolsables.</li>
                <li>La **reprogramación de citas no tiene costo adicional** y puede ser iniciada por el veterinario en casos de emergencia o por el cliente llamando directamente a nuestra administración. Esta es la única vía para postergar una cita.</li>
                <li>Si una cita pagada es postergada por el veterinario y el cliente no acepta la nueva fecha/hora, el cliente debe **contactar directamente al área de administración** (vía telefónica al **944280482** o presencialmente en nuestra dirección) para procesar su devolución. Es indispensable presentar el comprobante de pago enviado a su correo electrónico (voucher digital).</li>
            </ul>

            <p class="mb-0"><strong>2. Para Productos:</strong></p>
            <ul>
                <li>Todos los pagos realizados por la compra de productos son **definitivos y no son reembolsables**.</li>
                <li>En caso de recibir **productos defectuosos**, el cliente debe contactarnos de inmediato por correo electrónico a **blueyvet@gmail.com** o llamando al **944280482**. El área de administración verificará el error y le brindará una solución para el cambio del producto, la cual podría requerir la devolución física del producto defectuoso.</li>
            </ul>
            <p class="mt-2 text-muted">Agradecemos tu comprensión y colaboración con nuestras políticas para garantizar un servicio eficiente y justo para todos.</p>
        </div>

        <h2 class="h4 mt-4 mb-3">5. Propiedad Intelectual</h2>
        <p>Todo el contenido presente en el Servicio, incluyendo textos, gráficos, logotipos, iconos, imágenes, clips de audio, descargas digitales, compilaciones de datos y software, es propiedad de BlueyVet S.A.C. o de sus proveedores de contenido y está protegido por las leyes de propiedad intelectual de Perú e internacionales.</p>

        <h2 class="h4 mt-4 mb-3">6. Conducta del Usuario</h2>
        <p>Te comprometes a utilizar el Servicio de manera responsable y ética, respetando las leyes aplicables. Queda prohibido el uso del Servicio para fines ilegales o no autorizados, así como cualquier actividad que pueda dañar, deshabilitar, sobrecargar o perjudicar la infraestructura de BlueyVet o la de terceros.</p>

        <h2 class="h4 mt-4 mb-3">7. Limitación de Responsabilidad</h2>
        <p>BlueyVet S.A.C. no será responsable de ningún daño directo, indirecto, incidental, especial, consecuente o ejemplar, incluyendo, entre otros, daños por pérdida de beneficios, buena voluntad, uso, datos u otras pérdidas intangibles, que resulten de (i) el uso o la imposibilidad de usar el Servicio; (ii) el costo de adquisición de bienes y servicios sustitutos resultantes de cualquier bien, dato, información o servicios comprados u obtenidos o mensajes recibidos o transacciones realizadas a través o desde el Servicio; (iii) acceso no autorizado o alteración de tus transmisiones o datos; (iv) declaraciones o conducta de cualquier tercero en el Servicio; o (v) cualquier otro asunto relacionado con el Servicio. En ningún caso la responsabilidad total de BlueyVet excederá el monto pagado por el usuario por los servicios específicos que originaron la reclamación.</p>

        <h2 class="h4 mt-4 mb-3">8. Terminación</h2>
        <p>Podemos terminar o suspender tu acceso al Servicio inmediatamente, sin previo aviso ni responsabilidad, por cualquier motivo, incluyendo, entre otros, si incumples estos Términos de Servicio. Tras la terminación, tu derecho a usar el Servicio cesará inmediatamente.</p>

        <h2 class="h4 mt-4 mb-3">9. Ley Aplicable y Jurisdicción</h2>
        <p>Estos Términos de Servicio se regirán e interpretarán de acuerdo con las leyes de **Perú**, sin tener en cuenta sus principios de conflicto de leyes. Cualquier disputa que surja de o en relación con estos Términos de Servicio estará sujeta a la jurisdicción exclusiva de los tribunales de **Trujillo, La Libertad, Perú.**</p>

        <h2 class="h4 mt-4 mb-3">10. Contacto</h2>
        <p>Si tienes preguntas sobre estos Términos de Servicio, puedes contactarnos en:</p>
        <ul>
            <li><strong>Correo Electrónico:</strong> <a href="mailto:blueyvet@gmail.com">blueyvet@gmail.com</a></li>
            <li><strong>Teléfono:</strong> 944280482</li>
            <li><strong>Dirección:</strong> Av. Vía de Evitamiento Norte Km. 1.2, Trujillo 13009, Perú</li>
        </ul>

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