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
                display: ['"Archivo"', ...defaultTheme.fontFamily.sans],
                label: ['"Poppins"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'rx-accent': '#1652F0',
                'rx-accent-hover': '#0E3FC7',
                // Theme-aware tokens; values live in resources/css/theme.css.
                'rx-ink': 'rgb(var(--rx-ink) / <alpha-value>)',
                'rx-surface': 'rgb(var(--rx-surface) / <alpha-value>)',
                'rx-line': 'rgb(var(--rx-line) / <alpha-value>)',
                'rx-muted': 'rgb(var(--rx-muted) / <alpha-value>)',
            },
        },
    },

    plugins: [forms],
};
