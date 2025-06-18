import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Asegúrate de que host esté a true para permitir conexiones externas.
        // Esto le dice a Vite que escuche en 0.0.0.0, lo que permite conexiones desde cualquier IP.
        host: true,
        // Si sigues teniendo problemas con el HMR (Hot Module Replacement)
        // Puedes intentar configurar el host HMR explícitamente:
        // hmr: {
        //     host: 'localhost', // O la IP que Live Share esté usando para el proxy
        // },
        port: 5173, // Asegúrate de que este es el puerto correcto de Vite
    },
});
