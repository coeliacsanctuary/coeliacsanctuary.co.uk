import { defineConfig, UserConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ command, isSsrBuild }) => {
  let config: UserConfig = {
    plugins: [
      laravel({
        input: ['resources/js/app.ts', 'resources/js/standalone.ts'],
        ssr: 'resources/js/ssr.ts',
        refresh: true,
      }),
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
    ],
    optimizeDeps: {
      include: ['tailwind.config.ts'],
    },
    build: {
      commonjsOptions: {
        include: ['tailwind.config.ts', 'node_modules/**'],
      },
    },
    ssr: {
      external: ['i18n-iso-countries'],
    },
  };

  if (!isSsrBuild) {
    config['resolve'] = {
      alias: {
        vue: 'vue/dist/vue.esm-bundler.js',
      },
    };
  }

  return config;
});
