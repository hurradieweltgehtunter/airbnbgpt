import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        "./node_modules/flowbite/**/*.js"
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['TimesNewRoman', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                'blueGray': {
                    DEFAULT: '#2b3f57',
                },
                red: {
                    DEFAULT: '#ff0000',
                },
                white: {
                    DEFAULT: '#ffffff',
                },
                'rosePink': {
                    DEFAULT: '#f55f77',
                },
                'softPurple': {
                    DEFAULT: '#6c5b7b',
                },
                peachPink: {
                    '100': '#fdf6f3',
                    '200': '#f9e3df',
                    '300': '#f5d0cb',
                    '400': '#f1bdb7',
                    '500': '#f3b191', // Original color
                    '600': '#ef9e7d',
                    '700': '#eb8b69',
                    '800': '#e77855',
                    '900': '#e36541'
                },
                facebook: {
                    '': '#385499',
                    'hover': '#314a86'
                },
                google: '#dd4b39',
            }
        },
    },

    plugins: [forms, require('flowbite/plugin')],
};
