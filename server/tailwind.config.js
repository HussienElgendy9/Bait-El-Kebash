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
                arabic: ['"Hacen Tunisia"', 'sans-serif'],
                arabicLight: ['"Hacen Tunisia Lt"', 'sans-serif'],
                arabicBold: ['"Hacen Tunisia Bd"', 'sans-serif'],
            },
            colors:{
                brand:{
                    beige:'#f2e8cd',
                    green: '#004225',
                    greenish: '#004223',
                    brown: '#8b5e3c',
                    offwhite: '#fffdf7',
                    dark: '#2c2c2c',
                    light: '#fafafa',
                }
            },
            keyframes:{
                softbounce: {
                    '0%, 100%': {transform: 'translateY(0)'},
                    '50%': {transform:'translateY(-10px)'}
                }
            },
            animation:{
                softbounce: 'softbounce 2s infinite'
            }
        },
    },

    plugins: [forms],
};
