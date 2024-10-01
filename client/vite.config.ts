import { fileURLToPath, URL } from "node:url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// Finn mer her https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  build: {
    minify: true,  // optional: further optimizes the build
    sourcemap: false, // optional: disables sourcemaps
    chunkSizeWarningLimit : 1000, // size in KB
    rollupOptions: {
        output: {
            dir: './dist/assets/',
            entryFileNames: 'build.js',
            assetFileNames: 'build.css',
            chunkFileNames: "chunk.js",
            manualChunks: undefined,
        }
    }
  },
  define: {
    'process.env.NODE_ENV': '"production"',  // explicitly set production mode
  },
});
