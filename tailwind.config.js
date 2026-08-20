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
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
                cursive: ['Caveat', 'Dancing Script', 'cursive'],
            },
            colors: {
                bear: {
                    dark: '#4A240D',
                    brown: '#6B3718',
                    honey: '#E7A13B',
                    'honey-light': '#FDF3E7',
                    cream: '#FFF8EF',
                    'cream-light': '#FFFCF8',
                    border: '#E9DED3',
                    text: '#3B2418',
                    muted: '#8B817A',
                    error: '#C0392B',
                    success: '#2E7D32',
                },
            },
        },
    },

    plugins: [forms],
};

