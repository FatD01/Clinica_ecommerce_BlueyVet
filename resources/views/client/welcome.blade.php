@extends('layouts.app') {{-- CORRECCIÓN: Extiende client.layouts.app --}}

@section('title', 'Inicio')

@section('content')
<section class="hero bg-azul-claro">
    <div class="container hero-container">
        <div class="hero-texto">
            <h2 class="hero-titulo">
                ¡Bienvenidos a <br><span class="azul">BlueyVet</span>
            </h2>
            <p class="hero-subtitulo">
                Salud y bienestar para tu mascota, <br>tranquilidad para ti.
            </p>
            <a href="{{ route('login') }}" class="boton-unete" id="btn-unete-info">
                Únete a nosotros
            </a>
            <div class="linea-decorativa">
                <div class="linea-oscura"></div>
                <div class="linea-blanca"></div>
            </div>
        </div>
        <div class="hero-imagen">
            <img src="{{ asset('img/perrosgatos.png') }}" alt="Perro y gato" class="hero-img">
        </div>
    </div>
</section>

<section class="seccion-porque-elegir py-12 bg-blanco">
    <div class="container">
        <h2 class="titulo-seccion text-center">¿Por qué elegir BlueyVet?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="card-beneficio bg-azul-claro">
                <i class="bi bi-person-plus-fill icono-beneficio"></i>
                <h3 class="titulo-beneficio">Registro Sencillo</h3>
                <p class="texto-beneficio">Crea tu cuenta y accede a todos los beneficios.</p>
            </div>
            <div class="card-beneficio bg-azul-claro">
                <i class="bi bi-box-seam-fill icono-beneficio"></i>
                <h3 class="titulo-beneficio">Amplia Gama de Productos</h3>
                <p class="texto-beneficio">Todo lo que tu mascota necesita en un solo lugar.</p>
            </div>
            <div class="card-beneficio bg-azul-claro">
                <i class="bi bi-calendar-plus-fill icono-beneficio"></i>
                <h3 class="titulo-beneficio">Reserva de Citas</h3>
                <p class="texto-beneficio">Programa tus citas veterinarias de forma fácil.</p>
            </div>
            <div class="card-beneficio bg-azul-claro">
                <i class="bi bi-heart-fill icono-beneficio"></i>
                <h3 class="titulo-beneficio">Cuidado Integral</h3>
                <p class="texto-beneficio">Priorizamos la salud y el bienestar de tu mascota.</p>
            </div>
        </div>
    </div>
</section>

<section class="seccion-servicios py-12 bg-amarillo-claro">
    <div class="container">
        <h2 class="titulo-seccion text-center">Nuestros Servicios Destacados</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="card-servicio bg-blanco">
                <h3 class="titulo-servicio">Consultas Veterinarias</h3>
                <p class="texto-servicio">Atención médica profesional para tus compañeros.</p>
                <a href="#" class="enlace-servicio">Ver más</a>
            </div>
            <div class="card-servicio bg-blanco">
                <h3 class="titulo-servicio">Farmacia Veterinaria</h3>
                <p class="texto-servicio">Medicamentos y productos de salud de alta calidad.</p>
                <a href="#" class="enlace-servicio">Ver más</a>
            </div>
            <div class="card-servicio bg-blanco">
                <h3 class="titulo-servicio">Peluquería Canina y Felina</h3>
                <p class="texto-servicio">Servicios de estética para mantener a tu mascota feliz y saludable.</p>
                <a href="#" class="enlace-servicio">Ver más</a>
            </div>
        </div>
    </div>
</section>

<section class="seccion-blog py-12 bg-blanco">
    <div class="container">
        <h2 class="titulo-seccion text-center">Últimas Entradas del Blog</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="card-blog">
                <img src="https://via.placeholder.com/350x200" alt="Artículo del blog" class="imagen-blog">
                <div class="contenido-blog">
                    <h3 class="titulo-blog">Consejos para el cuidado de tu cachorro</h3>
                    <p class="texto-blog">Aprende los mejores consejos para darle la bienvenida a tu nuevo amigo.</p>
                    <a href="#" class="enlace-blog">Leer más</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnUneteInfo = document.getElementById('btn-unete-info');
        if (btnUneteInfo) {
            btnUneteInfo.addEventListener('click', function() {
                window.location.href = "{{ route('register') }}"; // Redirige al registro
            });
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, {
            threshold: 0.2
        });

        document.querySelectorAll('.card-beneficio, .card-servicio, .card-blog').forEach(card => {
            observer.observe(card);
        });
    });
</script>
@endpush