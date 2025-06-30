import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'bluey-dark': '#393859',
                'bluey-primary': ' #74bcec ', // Usando 'primary-blue' como 'bluey-primary'
                'bluey-light': '#CEE4F2', // Usando 'light-blue' como 'bluey-light'
                'bluey-light-yellow': '#F2DC6D',
                'bluey-gold-yellow': '#F2C879',
                'bluey-secondary': ' #e47c34 ',
                'bluey-secondary-light': ' #ddc06e ',
                'bluey-secondary-light2': '  #d6e9f0 ',
            }
        },
    },

    plugins: [
        require('@tailwindcss/typography'),
        forms],
    important: true,
};
