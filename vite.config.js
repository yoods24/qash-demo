// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/customer.css',
                'resources/js/customer.js',
                'resources/css/backoffice.css',
                'resources/js/backoffice.js',
                'resources/css/app.css'
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: '172.24.64.1',
        },
    },
});
