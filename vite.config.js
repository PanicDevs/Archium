import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

const rootPath = resolve(process.cwd(), '../../');

export default defineConfig({
    root: __dirname,
    plugins: [
        laravel({
            input: [
                'resources/css/archium.css',
                'resources/js/archium.js'
            ],
            hotFile: resolve(rootPath, 'public/hot'),
            buildDirectory: 'vendor/archium',
            refresh: true
        })
    ],
    build: {
        outDir: resolve(rootPath, 'public/vendor/archium'),
        emptyOutDir: true,
        manifest: 'manifest.json',
        rollupOptions: {
            input: [
                resolve(__dirname, 'resources/js/archium.js'),
                resolve(__dirname, 'resources/css/archium.css')
            ]
        }
    },
    base: '/vendor/archium/'
}); 