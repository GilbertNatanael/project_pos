import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/dashboard.css',
                'resources/js/dashboard.js',
                'resources/css/sidebar.css',
                'resources/js/sidebar.js',
                'resources/css/barang.css',
                'resources/js/barang.js',
                'resources/css/create_barang.css',
                'resources/js/create_barang.js',
                'resources/css/karyawan.css',
                'resources/js/karyawan.js',
                'resources/css/create_karyawan.css',
                'resources/js/transaksi.js',
                'resources/css/transaksi.css',
                'resources/js/prediksi.js',
                'resources/css/prediksi.css',
                'resources/js/history.js',
                'resources/css/history.css'
            ],
            refresh: true,
        }),
    ],
});
