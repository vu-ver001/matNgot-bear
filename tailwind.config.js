import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Montserrat', 'sans-serif'],
            },
            colors: {
                // Tone màu Nâu - Be Sữa - Mật Ong thương hiệu Mật Ngọt Bear
                brown: {
                    darker: 'var(--brown-darker, #2C160B)',
                    dark: 'var(--brown-dark, #3E1E0E)',
                    main: 'var(--brown-main, #5C3219)',
                    light: 'var(--brown-light, #7E4A28)',
                },
                honey: {
                    DEFAULT: 'var(--honey, #E09028)',
                    light: 'var(--honey-light, #F4B860)',
                    bg: 'var(--honey-bg, #FFF5E6)',
                },
                cream: {
                    bg: 'var(--cream-bg, #F9F5EE)',
                    card: 'var(--cream-card, #FAF6F0)',
                    input: 'var(--cream-input, #FFFFFF)',
                },
                bear: {
                    border: 'var(--border-color, #EBDDCD)',
                    focus: 'var(--border-focus, #DDA760)',
                    title: 'var(--text-title, #2E190E)',
                    body: 'var(--text-body, #615248)',
                    muted: 'var(--text-muted, #8E8076)',
                    error: 'var(--error-color, #E53E3E)',
                    errorBg: 'var(--error-bg, #FFF5F5)',
                }
            },
        },
    },

    plugins: [forms],
};
