@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">¡Pago Cancelado o Fallido!</h4>
        <p>La transacción de pago no se pudo completar o fue cancelada. Por favor, intenta de nuevo.</p>
        <hr>
        <p class="mb-0">Si el problema persiste, contacta a nuestro soporte.</p>
    </div>
    <a href="{{ route('client.servicios.index') }}" class="btn btn-primary mt-3">Volver a Servicios</a>
</div>
@endsection