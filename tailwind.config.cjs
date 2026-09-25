/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/Http/Livewire/**/*.php',
    './app/View/Components/**/*.php',
    './app/Http/Controllers/**/*.php',
    './routes/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        // Neutral palette (custom names)
        neutral: {
          primary: '#ffffff',        // white
          'primary-soft': '#f9fafb', // gray-50
          secondary: '#e5e7eb',      // gray-200
          'secondary-soft': '#f3f4f6', // gray-100
          'secondary-medium': '#d1d5db', // gray-300
          tertiary: '#9ca3af',       // gray-400
          'tertiary-medium': '#6b7280', // gray-500
        },
        // Brand color
        brand: {
          DEFAULT: '#16a34a',  // green-600
          strong: '#15803d',   // green-700
          medium: '#86efac',   // green-300 (for focus rings)
        },
        // Default border color
        default: '#e5e7eb', // gray-200
        // Text colors
        heading: '#111827', // gray-900
        body: '#4b5563',    // gray-600
      },
      borderRadius: {
        base: '0.5rem',
      },
      boxShadow: {
        xs: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('flowbite/plugin'),
  ],
};