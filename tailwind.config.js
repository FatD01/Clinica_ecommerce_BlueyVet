import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
            'bluey-dark': '#393859',
                'bluey-primary': '#85C8F2', // Usando 'primary-blue' como 'bluey-primary'
                'bluey-light': '#CEE4F2', // Usando 'light-blue' como 'bluey-light'
                'bluey-light-yellow': '#F2DC6D',
                'bluey-gold-yellow': '#F2C879',
            }
        },
    },

    plugins: [forms],
};
