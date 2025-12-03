/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      animation: {
        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
        'float': 'float 10s infinite ease-in-out',
        'ping': 'ping 1s cubic-bezier(0, 0, 0.2, 1) infinite',
        'bounce': 'bounce 1s infinite',
        'spin': 'spin 1s linear infinite',
      },
      keyframes: {
        fadeInUp: {
          'from': {
            opacity: '0',
            transform: 'translateY(20px)'
          },
          'to': {
            opacity: '1',
            transform: 'translateY(0)'
          },
        },
        float: {
          '0%, 100%': { transform: 'translate(0, 0)' },
          '50%': { transform: 'translate(20px, -20px)' },
        },
      },
      backdropBlur: {
        xs: '2px',
      },
      boxShadow: {
        'glow-indigo': '0 0 20px rgba(99, 102, 241, 0.3)',
        'glow-purple': '0 0 20px rgba(168, 85, 247, 0.3)',
      },
      colors: {
        indigo: {
          50: '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
        },
      },
    },
  },
  plugins: [],
}
