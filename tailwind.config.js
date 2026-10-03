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
          primary: 'var(--mkv-color-primary)',
          'primary-hover': 'var(--mkv-color-primary-hover)',
          'primary-dark': 'var(--mkv-color-primary-hover)',
          'primary-light': 'var(--mkv-color-primary-subtle)',
          'primary-subtle': 'var(--mkv-color-primary-subtle)',
          green: 'var(--mkv-color-success)',
          'green-light': 'var(--mkv-color-success-subtle)',
          red: 'var(--mkv-color-danger)',
          'red-light': 'var(--mkv-color-danger-subtle)',
          yellow: 'var(--mkv-color-warning)',
          'yellow-light': 'var(--mkv-color-warning-subtle)',
          purple: 'var(--mkv-color-purple)',
          'purple-light': 'var(--mkv-color-purple-subtle)',
          blue: 'var(--mkv-color-primary)',
          bg: 'var(--mkv-color-bg)',
          surface: 'var(--mkv-color-surface)',
          'surface-subtle': 'var(--mkv-color-surface-subtle)',
          'surface-muted': 'var(--mkv-color-surface-muted)',
          'text-main': 'var(--mkv-color-text-primary)',
          'text-primary': 'var(--mkv-color-text-primary)',
          'text-secondary': 'var(--mkv-color-text-secondary)',
          'text-muted': 'var(--mkv-color-text-muted)',
          border: 'var(--mkv-color-border)',
          'border-light': 'var(--mkv-color-border-subtle)',
          'border-subtle': 'var(--mkv-color-border-subtle)',
          'border-strong': 'var(--mkv-color-border-strong)',
        }
      },
      fontFamily: {
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        mono: ['JetBrains Mono', 'SF Mono', 'Consolas', 'Monaco', 'monospace'],
      },
      boxShadow: {
        'mkv-xs': 'var(--mkv-shadow-xs)',
        'mkv-sm': 'var(--mkv-shadow-sm)',
        'mkv-md': 'var(--mkv-shadow-md)',
        'mkv-lg': 'var(--mkv-shadow-lg)',
        'mkv-xl': 'var(--mkv-shadow-xl)',
      },
      borderRadius: {
        'mkv-xs': 'var(--mkv-radius-xs)',
        'mkv-sm': 'var(--mkv-radius-sm)',
        'mkv-md': 'var(--mkv-radius-md)',
        'mkv-lg': 'var(--mkv-radius-lg)',
        'mkv-xl': 'var(--mkv-radius-xl)',
      },
      spacing: {
        'mkv-1': 'var(--mkv-space-1)',
        'mkv-2': 'var(--mkv-space-2)',
        'mkv-3': 'var(--mkv-space-3)',
        'mkv-4': 'var(--mkv-space-4)',
        'mkv-5': 'var(--mkv-space-5)',
        'mkv-6': 'var(--mkv-space-6)',
        'mkv-8': 'var(--mkv-space-8)',
      }
    },
  },
  plugins: [],
}
