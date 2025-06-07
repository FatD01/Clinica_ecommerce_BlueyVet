<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage; // <--- ¡Añade esta línea!

Route::get('/', function () {
    return view('welcome');
});



// Route::get('/test-image', function () {

//     $imagePath = 'product-images/01JWNG4599P3A58WSY0C0KV9TH.jpg'; // Asegúrate de que este archivo existe
//     dd(asset('storage/' . $imagePath), Storage::url($imagePath));
// });

