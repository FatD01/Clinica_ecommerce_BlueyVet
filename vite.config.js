import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js',    'resources/css/app-vet.css', 'resources/js/app-vet.js',
                 'resources/css/Vet/views/citasagendadas.css',
                'resources/css/Vet/views/datosestadisticos.css',
                'resources/css/Vet/views/historialmascota.css',
      
                'resources/css/Vet/views/info.css',            // ¡Nuevo!
                // 'resources/css/Vet/global/filtros.css',
                  'resources/css/Vet/views/vermascotas.css',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '127.0.0.1', // <--- Esto fuerza a Vite a usar localhost (IPv4)
        port: 5173, // <--- Este es el puerto predeterminado, asegúrate de que sea este.
        hmr: {
            host: '127.0.0.1', // <--- Necesario para Hot Module Replacement
        },
    },
});
