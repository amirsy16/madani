import preset from './vendor/filament/support/tailwind.config.preset.js'

/** @type {import('tailwindcss').Config} */
export default {
  // Preset Filament wajib: menyediakan warna custom-* (tombol bg-custom-600 dsb)
  // dan scan semua blade vendor/filament agar kelasnya ter-generate.
  presets: [preset],
  // Wajib 'class': Filament mengelola tema light/dark lewat class di <html>;
  // kalau 'media' (default), tema ikut setting OS dan switcher light/dark tidak berfungsi.
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./vendor/filament/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        // Palet primary = brand maroon (#800020 sekeluarga rose).
        // Diperlukan karena banyak view memakai kelas primary-* (bg-primary-600 dll),
        // padahal "primary" di Filament panel bukan warna Tailwind sehingga
        // kelasnya tidak pernah ter-generate sebelum palet ini didaftarkan.
        primary: {
          50: '#fff1f2',
          100: '#ffe4e6',
          200: '#fecdd3',
          300: '#fda4af',
          400: '#fb7185',
          500: '#f43f5e',
          600: '#e11d48',
          700: '#be123c',
          800: '#9f1239',
          900: '#881337',
        },
        madani: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
