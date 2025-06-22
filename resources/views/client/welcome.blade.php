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




{{-- ================================================================= --}}
{{-- SECCIÓN DEL BLOG - DISEÑO MODERNO SUPERPUESTO --}}
{{-- ================================================================= --}}
<section class="bg-bluey-light py-16 sm:py-20">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">

    <div class="max-w-2xl mx-auto text-center mb-16">
      <h2 class="text-3xl font-bold tracking-tight text-bluey-dark sm:text-4xl">
        BlogPet
      </h2>
      <p class="mt-4 text-lg leading-8 text-bluey-dark/80">
        Descubre nuestras recomendaciones y experiencias compartidas
      </p>
    </div>

    @if($recentPosts->isNotEmpty())
      <div class="grid gap-0 md:gap-8 lg:gap-12">
        @foreach($recentPosts as $post)
          <div class="relative group" data-aos="fade-up">
            {{-- Contenedor principal con efecto de superposición --}}
            <div class="relative z-10 @if($loop->odd) ml-0 md:ml-8 lg:ml-16 @else mr-0 md:mr-8 lg:mr-16 @endif 
                        mt-8 hover:mt-4 transition-all duration-500">
              
              {{-- Tarjeta base (sombra) --}}
              <div class="absolute inset-0 bg-bluey-primary rounded-xl 
                         @if($loop->odd) rotate-2 @else -rotate-2 @endif
                         translate-y-4 group-hover:translate-y-2 transition-transform duration-500"></div>
              
              {{-- Tarjeta principal --}}
              <div class="relative bg-white rounded-xl shadow-lg overflow-hidden border-2 border-bluey-light/20 
                         group-hover:border-bluey-primary/40 transition-all duration-500">
                
                {{-- Imagen con efecto de vidrio esmerilado --}}
                @if($post->image_path)
                <a href="{{ route('blog.show', $post->slug) }}" class="block relative overflow-hidden h-48">
                  <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}" 
                       class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110">
                  <div class="absolute inset-0 bg-gradient-to-b from-bluey-dark/10 to-bluey-dark/50 
                              mix-blend-multiply transition-opacity duration-500 group-hover:opacity-70"></div>
                </a>
                @endif

                {{-- Contenido --}}
                <div class="p-6 relative">
                  {{-- Etiqueta de fecha flotante --}}
                  <div class="absolute -top-5 right-6 bg-bluey-primary text-white px-3 py-1 rounded-full 
                              text-xs font-bold shadow-md transition-all duration-300 group-hover:bg-bluey-secondary">
                    {{ $post->created_at->translatedFormat('d M, Y') }}
                  </div>
                  
                  <h3 class="text-xl font-bold text-bluey-dark mb-3 pr-8">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-bluey-primary transition-colors duration-300">
                      {{ $post->title }}
                    </a>
                  </h3>
                  
                  <p class="text-bluey-dark/70 mb-5">
                    {{ $post->excerpt ? $post->excerpt : Str::limit(strip_tags($post->content), 120) }}
                  </p>
                  
                  <a href="{{ route('blog.show', $post->slug) }}" 
                     class="inline-flex items-center font-medium text-bluey-primary hover:text-bluey-secondary 
                            transition-colors duration-300 border-b-2 border-transparent hover:border-bluey-secondary">
                    Leer más
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform duration-300 group-hover:translate-x-1" 
                         viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-12 bg-bluey-light-yellow/30 rounded-xl max-w-2xl mx-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-bluey-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-bluey-dark">Blog en construcción</h3>
        <p class="mt-2 text-bluey-dark/60">Estamos preparando contenido valioso para ti</p>
      </div>
    @endif

    {{-- Botón para ver más (opcional) --}}
    <div class="text-center mt-16">
      <a href="{{ route('blog.index') }}" class="inline-flex items-center px-6 py-3 bg-bluey-primary text-white font-medium rounded-full 
                                                hover:bg-bluey-dark transition-all duration-300 shadow-md hover:shadow-lg">
        Ver todos los artículos
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 -mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </a>
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