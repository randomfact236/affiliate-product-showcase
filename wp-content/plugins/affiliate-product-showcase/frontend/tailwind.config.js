/** @type {import('tailwindcss').Config} */
export default {
  prefix: 'aps-',
  content: [
    "./templates/**/*.php",
    "./src/**/*.js",
    "./index.html"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
