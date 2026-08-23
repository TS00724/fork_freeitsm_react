import react from '@vitejs/plugin-react';
import { configDefaults, defineConfig } from 'vitest/config';

const phpOrigin = process.env.VITE_DEV_PHP_ORIGIN?.trim();

export default defineConfig({
  // Relative assets plus the runtime <base> allow a reviewed host to mount dist/
  // below a FreeITSM subdirectory without writing build output into PHP sources.
  base: './',
  plugins: [react()],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    sourcemap: false,
    // Used by verify-bundle-budget.mjs to distinguish the startup entry from
    // route-level dynamic chunks without adding a bundle-analyzer dependency.
    manifest: true
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
    exclude: [...configDefaults.exclude, 'e2e/**'],
    css: true,
    restoreMocks: true,
    clearMocks: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json-summary', 'html'],
      include: ['src/**/*.{ts,tsx}'],
      exclude: ['src/main.tsx', 'src/vite-env.d.ts'],
      thresholds: {
        statements: 80,
        branches: 75,
        functions: 80,
        lines: 80
      }
    }
  }
});
