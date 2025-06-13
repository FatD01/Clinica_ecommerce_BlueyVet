@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <div class="alert alert-success" role="alert">
        <h4 class="alert-heading">¡Pago Exitoso!</h4>
        <p>Tu transacción ha sido completada correctamente. Gracias por tu compra.</p>
        <hr>
        <p class="mb-0">Serás redirigido para completar tu cita o ver tus servicios.</p>
    </div>
    <a href="{{ route('client.home') }}" class="btn btn-primary mt-3">Volver al Inicio</a>
</div>
@endsection