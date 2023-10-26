import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/bootstrap.css',
                'resources/css/circular-style.css',
                'resources/css/font-awesome.css',
                'resources/css/style.css',
                'resources/css/icons.css',
                'resources/css/ubold.css',
                'resources/js/app.js',
                'resources/js/ubold_vendor.min.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
            ],
        }),
    ],
});
