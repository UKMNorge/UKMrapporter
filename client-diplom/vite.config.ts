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
    rollupOptions: {
        output: {
            dir: './dist/assets/',
            entryFileNames: 'build.js',
            assetFileNames: 'build.css',
            chunkFileNames: "chunk.js",
            manualChunks: undefined,
        }
    }
}
});
