@extends('layouts.app')

@section('title', 'Servicios')

@section('content')

<section class="hero-section">
    <div class="hero-container">
        <img src="{{ asset('img/fondo_servicios.jpg')}}" alt="Consultas Veterinarias" class="hero-image">
        <div class="hero-content">
            <h1 class="hero-title">Servicios Veterinarios de Calidad</h1>
            <p class="hero-text">Cuidamos a tus mascotas con amor y profesionalismo</p>
            <a href="#formulario-cita-correo" class="hero-button">Reservar Servicio</a>
        </div>
    </div>
</section>

<section class="section-team py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 section-title">Nuestro Equipo Veterinario</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
            @foreach($veterinarios as $vet)
            <div class="col">
                <div class="card h-100 shadow-sm text-center team-card">
                    {{-- Accede a la imagen del usuario relacionado si está en la tabla users --}}
                    {{-- Si el usuario tiene una foto de perfil (ej. de Breeze/Jetstream), usa user->profile_photo_url --}}
                    {{-- Si no, usa una imagen por defecto o ajusta la lógica a tu caso --}}
                    <img src="{{ asset($vet->user->profile_photo_url ?? 'img/default-vet.jpg') }}" alt="{{ $vet->user->name ?? 'Veterinario sin nombre' }}" class="card-img-top rounded-circle mx-auto mt-3 team-img">
                    <div class="card-body">
                        {{-- Accede al nombre del usuario relacionado --}}
                        <h5 class="card-title">{{ $vet->user->name ?? 'Nombre No Disponible' }}</h5>
                        {{-- Accede directamente a la especialidad de la tabla veterinarians --}}
                        <p class="card-text">{{ $vet->specialty ?? 'Especialidad No Disponible' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-prices py-5">
    <div class="container">
        <h2 class="text-center mb-5 section-title">Servicios y Precios</h2>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Servicio</th>
                        <th scope="col">Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicios as $servicio)
                    <tr>
                        {{-- Asumo que tu tabla 'services' tiene 'name' y 'price' --}}
                        <td>{{ $servicio->name }}</td>
                        <td>S/{{ number_format($servicio->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-contact py-5 bg-light" id="formulario-cita-correo">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">RESERVA TU CITA POR CORREO</h3>
                        <p class="mb-0">Envíanos un mensaje y te contactaremos para confirmar.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('client.servicios.send-contact-mail') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Tus nombres:</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">Tus apellidos:</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono:</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="servicio" class="form-label">Servicio:</label>
                                    <select class="form-select" id="servicio" name="servicio" required>
                                        <option value="">Selecciona un servicio</option>
                                        @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->name }}">{{ $servicio->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="veterinario" class="form-label">Veterinario de preferencia:</label>
                                    <select class="form-select" id="veterinario" name="veterinario" required>
                                        <option value="">Cualquier Veterinario</option>
                                        @foreach($veterinarios as $vet)
                                        <option value="{{ $vet->user->name ?? 'Nombre no disponible' }}">{{ $vet->user->name ?? 'Nombre no disponible' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label">Fecha deseada:</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="hora" class="form-label">Hora deseada:</label>
                                    <input type="time" class="form-control" id="hora" name="hora" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="mensaje" class="form-label">Mensaje adicional (opcional):</label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="4" placeholder="Ej: Mi mascota tiene 5 años y es un chihuahua."></textarea>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    He leído y acepto la <a href="https://www.essalud.gob.pe/downloads/POLITICA_DE_PRIVACIDAD_PARA_EL_TRATAMIENTO_DE_DATOS_PERSONALES.pdf" target="_blank" class="text-primary">Política de privacidad</a>.
                                </label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-lg custom-btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i> Enviar Solicitud
                                </button>
                                <button type="reset" class="btn btn-secondary btn-lg custom-btn-secondary">
                                    <i class="fas fa-eraser me-2"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('js')
    {{-- Cualquier JS específico para servicios si lo necesitas --}}
@endpush