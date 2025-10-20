// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/customer.css',
                'resources/js/customer.js',
                'resources/css/backoffice.css',
                'resources/js/backoffice.js',
                'resources/css/app.css',
                'resources/css/filament.css',
            ],
            refresh: true,
        }),
    tailwindcss(),
    ],
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     hmr: {
    //         host: '172.24.64.1',
    //     },
    // },
});
