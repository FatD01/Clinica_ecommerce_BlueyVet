@extends('layouts.app') {{-- Ajusta 'layouts.app' si tu plantilla base tiene otro nombre --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Registrar Nueva Mascota</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('client.mascotas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre de la Mascota</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        {{-- CAMBIO AQUÍ: input text a select --}}
                        <div class="mb-3">
                            <label for="species" class="form-label">Especie</label>
                            <select class="form-select" id="species" name="species" required>
                                <option value="">Selecciona una especie</option>
                                <option value="Perro" {{ old('species') == 'Perro' ? 'selected' : '' }}>Perro</option>
                                <option value="Gato" {{ old('species') == 'Gato' ? 'selected' : '' }}>Gato</option>
                                {{-- Si tienes más tipos, añádelos aquí --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="race" class="form-label">Raza (Opcional)</label>
                            <input type="text" class="form-control" id="race" name="race" value="{{ old('race') }}">
                        </div>

                        <div class="mb-3">
                            <label for="weight" class="form-label">Peso (kg - Opcional)</label>
                            <input type="number" step="0.01" class="form-control" id="weight" name="weight" value="{{ old('weight') }}">
                        </div>

                        <div class="mb-3">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento (Opcional)</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                        </div>

                        <div class="mb-3">
                            <label for="allergies" class="form-label">Alergias (Opcional)</label>
                            <textarea class="form-control" id="allergies" name="allergies" rows="3">{{ old('allergies') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Avatar (Opcional)</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-success">Registrar Mascota</button>
                        <a href="{{ route('client.mascotas.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection