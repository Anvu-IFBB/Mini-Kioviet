/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./includes/views/**/*.php",
    "./includes/admin/**/*.php",
    "./assets/js/**/*.js",
    "./*.php"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        mkv: {
          primary: '#0052cc',
          'primary-dark': '#003d99',
          'primary-light': '#e6f0ff',
          green: '#16a34a',
          'green-light': '#f0fdf4',
          red: '#dc2626',
          'red-light': '#fef2f2',
          yellow: '#d97706',
          'yellow-light': '#fffbeb',
          purple: '#7c3aed',
          'purple-light': '#f5f3ff',
          blue: '#2563eb',
          bg: '#f2f3f5',
          surface: '#ffffff',
          'text-main': '#1e293b',
          'text-muted': '#64748b',
          border: '#e2e8f0',
          'border-light': '#f8fafc',
        }
      },
      fontFamily: {
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
      },
      boxShadow: {
        'mkv-sm': '0 1px 2px rgba(0,0,0,.04)',
        'mkv-md': '0 2px 8px rgba(0,0,0,.06)',
        'mkv-lg': '0 8px 24px rgba(0,0,0,.1)',
      }
    },
  },
  plugins: [],
}
