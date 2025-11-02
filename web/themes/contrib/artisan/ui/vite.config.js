import { defineConfig } from 'vite';
import { resolve,relative } from 'path';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
  return {
    plugins: [tailwindcss()],
    build: {
      assetsInlineLimit: 0,
      manifest: false,
      outDir: 'dist',
      rollupOptions: {
        input: [
          resolve(__dirname, 'src/artisan-customizer.ts'),
          resolve(__dirname, 'src/artisan-customizer.css'),
        ],
        output: {
          entryFileNames:'[name].js',
          chunkFileNames: '[name].js',
          assetFileNames: '[name].[ext]',
        }
      },
      emptyOutDir: true
    }
  };
});
