import typescript from '@rollup/plugin-typescript';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
  input: 'artisan-customizer.ts',
  output: {
    file: 'artisan-customizer.js',
    format: 'iife',
    name: 'ArtisanCustomizer',
  },
  plugins: [
    resolve(),
    commonjs(),
    typescript(),
  ],
};
