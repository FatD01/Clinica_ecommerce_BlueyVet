@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">¡Ocurrió un Error!</h4>
        <p>Hubo un problema inesperado al procesar tu pago. Lamentamos los inconvenientes.</p>
        <hr>
        <p class="mb-0">Por favor, inténtalo de nuevo o contacta a soporte para asistencia.</p>
    </div>
    <a href="{{ route('client.servicios.index') }}" class="btn btn-primary mt-3">Volver a Servicios</a>
</div>
@endsection