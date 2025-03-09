import './bootstrap';
import '../css/app.css';
import { Component, createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import Coeliac from '@/Layouts/Coeliac.vue';
import ArticleHeader from '@/Components/ArticleHeader.vue';
import ArticleImage from '@/Components/ArticleImage.vue';
import { InertiaPage } from '@/types/Core';
import { getTitle } from '@/helpers';
import AnalyticsTrack from '@/analyticsTrack';

void createInertiaApp({
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

  setup({ el, App, props, plugin }) {
    const pinia = createPinia();

    createApp({ render: () => h(App, props) })
      .component('article-header', ArticleHeader as Component)
      .component('article-image', ArticleImage as Component)
      .use(plugin)
      .use(pinia)
      .mount(el);
  },
});

AnalyticsTrack();
