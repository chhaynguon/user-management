/** @type {import('tailwindcss').Config} */
import PrimeUI from 'tailwindcss-primeui';
export default {
  content: ['./resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',],
  theme: {
    screens: {
      sm: '576px',
      md: '768px',
      lg: '992px',
      xl: '1200px',
      '2xl': '1920px'
    },
    extend: {},
  },
  plugins: [PrimeUI],
}

 