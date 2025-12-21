/** @type {import('tailwindcss').Config} */
// Force rebuild
export default {
  // Disable dark mode by using a selector that is never applied
  darkMode: ['selector', '[data-theme="dark"]'],
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
