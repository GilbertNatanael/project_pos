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
                'resources/css/create_kategori.css',
                'resources/js/create_kategori.js',
                'resources/css/karyawan.css',
                'resources/js/karyawan.js',
                'resources/css/kategori.css',
                'resources/js/kategori.js',
                'resources/css/create_karyawan.css',
                'resources/js/transaksi.js',
                'resources/css/transaksi.css',
                'resources/js/forecast.js',
                'resources/css/forecast.css',
                'resources/js/history.js',
                'resources/css/history.css',
                'resources/js/pembelian.js',
                'resources/css/pembelian.css',
                'resources/js/tambah_pembelian.js',
                'resources/css/tambah_pembelian.css',
                'resources/js/laporan.js',
                'resources/css/laporan.css',
                'resources/js/cek_prediksi.js',
                'resources/css/cek_prediksi.css',
                'resources/js/detail_prediksi.js',
                'resources/css/detail_prediksi.css'
            ],
            refresh: true,
        }),
    ],
});
