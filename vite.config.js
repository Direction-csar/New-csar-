import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'public/css/responsive-optimizations.css',
                'public/js/responsive-charts.js',
                'public/js/performance-optimizer.js',
                'public/js/browser-compatibility.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Optimize for production
        minify: 'esbuild',
        // esbuild is faster and included by default
        rollupOptions: {
            output: {
                manualChunks: {
                    utils: ['public/js/responsive-charts.js', 'public/js/performance-optimizer.js']
                }
            }
        },
        // Optimize CSS
        cssCodeSplit: true,
        // Asset optimization
        assetsInlineLimit: 4096,
        // Source maps for debugging
        sourcemap: false,
    },
    server: {
        // Development server configuration
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
    // Optimize dependencies
    optimizeDeps: {
        // chart.js and leaflet are loaded via CDN in Blade templates
    }
});
