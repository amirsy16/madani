import preset from './vendor/filament/filament/tailwind.config.preset.js'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.{php,blade.php}',
        './resources/views/**/*.{php,blade.php}',
        './resources/css/filament/**/*.css',
        './vendor/filament/**/*.blade.php',
    ],
}
