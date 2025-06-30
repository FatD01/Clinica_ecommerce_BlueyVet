<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mascota;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class MascotaController extends Controller
{
    // NO se usa más el constructor para el middleware en Laravel 11+ / 12
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Define los middlewares para las acciones del controlador.
     * En Laravel 11+, esta es la forma recomendada.
     *
     * @return array<string, \Illuminate\Contracts\Routing\Middleware|string>
     */
    protected function middleware(): array
    {
        return [
            'auth', // Aplica el middleware 'auth' a todas las acciones de este controlador
            // También puedes especificar 'only' o 'except' para acciones específicas:
            // 'auth:web' => ['except' => ['show', 'index']], // Ejemplo: aplicar 'auth' a todo excepto show e index
            // 'auth' => ['only' => ['create', 'store', 'edit', 'update', 'destroy']], // Ejemplo: aplicar 'auth' solo a estas acciones
        ];
    }

    /**
     * Muestra la lista de mascotas del cliente autenticado.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Session::flash('error', 'No se encontró un perfil de cliente asociado a su cuenta. Por favor, complete su perfil.');
            return redirect()->route('dashboard'); // Cambia 'dashboard' por la ruta adecuada donde el cliente pueda crear su perfil.
        }

        $mascotas = $cliente->mascotas;
        return view('client.mascotas.index', compact('mascotas'));
    }

    /**
     * Muestra el formulario para registrar una nueva mascota.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!Auth::user()->cliente) {
            Session::flash('error', 'Debe tener un perfil de cliente para registrar mascotas.');
            return redirect()->route('dashboard');
        }
        return view('client.mascotas.create');
    }

    public function show(Mascota $mascota)
    {
        // Asegúrate de que la mascota pertenezca al cliente autenticado
        // if (Auth::user()->cliente->id !== $mascota->cliente_id) {
        //     Session::flash('error', 'Acceso no autorizado a esta mascota.');
        //     return redirect()->route('client.mascotas.index');
        // }

        // Carga los recordatorios de la mascota
        // Asegúrate de tener la relación 'reminders' definida en tu modelo Mascota
        $mascota->load('reminders');

        return view('client.mascotas.show', compact('mascota'));
    }


    /**
     * Almacena una nueva mascota en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'species' => ['required', 'string', Rule::in(['Perro', 'Gato'])],
            'race' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0.01',
            'birth_date' => 'nullable|date',
            'allergies' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Session::flash('error', 'No se encontró el perfil de cliente asociado. Por favor, complete su perfil.');
            return redirect()->back()->withInput();
        }

        $mascota = $cliente->mascotas()->create([
            'name' => $validatedData['name'],
            'species' => $validatedData['species'],
            'race' => $validatedData['race'],
            'weight' => $validatedData['weight'],
            'birth_date' => $validatedData['birth_date'],
            'allergies' => $validatedData['allergies'],
        ]);

        if ($request->hasFile('avatar')) {
            $mascota->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        Session::flash('success', '¡Mascota registrada exitosamente!');
        return redirect()->route('client.mascotas.index');
    }

    /**
     * Muestra el formulario para editar una mascota existente.
     *
     * @param  \App\Models\Mascota  $mascota
     * @return \Illuminate\View\View
     */
    public function edit(Mascota $mascota)
    {
        if (Auth::user()->cliente->id !== $mascota->cliente_id) {
            Session::flash('error', 'Acceso no autorizado a esta mascota.');
            return redirect()->route('client.mascotas.index');
        }

        return view('client.mascotas.edit', compact('mascota'));
    }

    /**
     * Actualiza una mascota existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mascota  $mascota
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Mascota $mascota)
    {
        if (Auth::user()->cliente->id !== $mascota->cliente_id) {
            Session::flash('error', 'Acceso no autorizado para actualizar esta mascota.');
            return redirect()->route('client.mascotas.index');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'species' => ['required', 'string', Rule::in(['Perro', 'Gato'])],
            'race' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0.01',
            'birth_date' => 'nullable|date',
            'allergies' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $mascota->update([
            'name' => $validatedData['name'],
            'species' => $validatedData['species'],
            'race' => $validatedData['race'],
            'weight' => $validatedData['weight'],
            'birth_date' => $validatedData['birth_date'],
            'allergies' => $validatedData['allergies'],
        ]);

        if ($request->hasFile('avatar')) {
            $mascota->clearMediaCollection('avatars');
            $mascota->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        Session::flash('success', '¡Mascota actualizada exitosamente!');
        return redirect()->route('client.mascotas.index');
    }

    /**
     * Elimina una mascota de la base de datos (soft delete).
     *
     * @param  \App\Models\Mascota  $mascota
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Mascota $mascota)
    {
        if (Auth::user()->cliente->id !== $mascota->cliente_id) {
            Session::flash('error', 'Acceso no autorizado para eliminar esta mascota.');
            return redirect()->route('client.mascotas.index');
        }

        $mascota->delete();

        Session::flash('success', 'Mascota eliminada exitosamente.');
        return redirect()->route('client.mascotas.index');
    }
}
