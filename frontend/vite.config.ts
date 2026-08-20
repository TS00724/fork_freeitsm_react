import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

const phpOrigin = process.env.VITE_DEV_PHP_ORIGIN?.trim();

export default defineConfig({
  // Relative assets plus the runtime <base> allow a reviewed host to mount dist/
  // below a FreeITSM subdirectory without writing build output into PHP sources.
  base: './',
  plugins: [react()],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    sourcemap: true
  },
  server: {
    port: 5173,
    strictPort: true,
    proxy: phpOrigin
      ? {
          '/api': { target: phpOrigin, changeOrigin: false, secure: false },
          '/auth': { target: phpOrigin, changeOrigin: false, secure: false }
        }
      : undefined
  },
  preview: { port: 4173, strictPort: true },
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
    css: true,
    restoreMocks: true,
    clearMocks: true
  }
});
