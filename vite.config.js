import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'vue': path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js')
        }
    },
    define: {
        '__VUE_OPTIONS_API__': true,  // включение Composition API
        '__VUE_PROD_DEVTOOLS__': false,  // отключение devtools в продакшене
        '__VUE_PROD_HYDRATION_MISMATCH_DETAILS__': false,  // управление hydration mismatch
    }
});
