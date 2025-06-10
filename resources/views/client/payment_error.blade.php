@extends('layouts.app') {{-- O el layout principal que uses --}}

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">¡Ha Ocurrido un Error!</h3>
                    </div>
                    <div class="card-body">
                        @if(Session::has('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ Session::get('error') }}
                            </div>
                        @else
                            <div class="alert alert-danger" role="alert">
                                Lo sentimos, hubo un problema inesperado al procesar tu solicitud. Por favor, intenta de nuevo.
                            </div>
                        @endif

                        <p>Si el problema persiste, contacta con nuestro equipo de soporte proporcionando los detalles del error si los tienes.</p>

                        <a href="{{ route('client.home') }}" class="btn btn-primary mt-3">Volver al inicio</a>
                        <a href="{{ route('client.servicios.index') }}" class="btn btn-secondary mt-3 ms-2">Volver a Servicios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection