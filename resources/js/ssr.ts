import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { Component, createSSRApp, h } from 'vue';
import { InertiaPage } from '@/types/Core';
import Coeliac from '@/Layouts/Coeliac.vue';
import { createPinia } from 'pinia';
import ArticleHeader from '@/Components/ArticleHeader.vue';
import ArticleImage from '@/Components/ArticleImage.vue';
import { getTitle } from '@/helpers';
import AnalyticsTrack from '@/analyticsTrack';

createServer((page) =>
  createInertiaApp({
    page,

    render: renderToString,

    title: getTitle,

    progress: {
      color: '#4B5563',
    },

    resolve: async (name) => {
      // @ts-ignore
      const pages: Record<string, () => Promise<() => InertiaPage>> =
        import.meta.glob('./Pages/**/*.vue');

      // @ts-ignore
      const page: InertiaPage = await pages[`./Pages/${name}.vue`]();

      page.default.layout = page.default.layout || (Coeliac as Component);

      return page;
    },

    setup({ App, props, plugin }) {
      const pinia = createPinia();

      return createSSRApp({ render: () => h(App, props) })
        .component('article-header', ArticleHeader as Component)
        .component('article-image', ArticleImage as Component)
        .use(plugin)
        .use(pinia);
    },
  }),
);

AnalyticsTrack();
