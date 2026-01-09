import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'assets/dist',
    emptyOutDir: false,
    manifest: true,
    rollupOptions: {
      input: {
        admin: 'frontend/js/admin.js',
        frontend: 'frontend/js/frontend.js',
        blocks: 'frontend/js/blocks.js'
      }
    }
  }
});
