import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import Components from 'unplugin-vue-components/vite';
import { PrimeVueResolver } from '@primevue/auto-import-resolver';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/assets/styles.scss'
            ],
            refresh: true,
        }),

        vue(),

        Components({
            resolvers: [PrimeVueResolver()]
        }),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@service': path.resolve(__dirname, 'resources/js/service'),
        },
    },

    server: {
        host: '127.0.0.1',
    },

    build: {
        chunkSizeWarningLimit: 1600,
    },
});
