import { defineConfig } from 'vite';

export default defineConfig({
	build: {
		outDir: 'assets/dist',
		emptyOutDir: true,
		manifest: true,
		rollupOptions: {
			input: {
				admin: 'src/js/admin.js',
				frontend: 'src/js/frontend.js',
				blocks: 'src/js/blocks.js'
			}
		}
	}
});
