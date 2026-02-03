/**
 * Vite Configuration - Single Entry Build
 * 
 * Pure Tailwind CSS approach with single entry point.
 * Outputs: dist/assets/main-[hash].css and dist/assets/main-[hash].js
 * 
 * @package AffiliateProductShowcase
 * @version 1.0.0
 */

import { defineConfig } from 'vite';
import { resolve } from 'path';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

export default defineConfig(({ mode }) => {
  const isProd = mode === 'production';
  
  return {
    root: '.',
    base: './',
    
    build: {
      outDir: 'dist',
      emptyOutDir: true,
      manifest: true,
      sourcemap: isProd ? false : true,
      minify: isProd,
      cssCodeSplit: false, // Single CSS file
      
      rollupOptions: {
        input: {
          main: resolve(__dirname, 'src/js/main.ts'),
        },
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          assetFileNames: (assetInfo) => {
            const info = assetInfo.name.split('.');
            const ext = info[info.length - 1];
            if (ext === 'css') {
              return 'assets/[name]-[hash][extname]';
            }
            return 'assets/[name]-[hash][extname]';
          },
        },
      },
    },
    
    css: {
      postcss: {
        plugins: [
          tailwindcss(resolve(__dirname, 'tailwind.config.js')),
          autoprefixer(),
        ],
      },
    },
    
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src'),
        '@css': resolve(__dirname, 'src/css'),
        '@js': resolve(__dirname, 'src/js'),
      },
    },
  };
});
