import './bootstrap';
import '../css/app.css';
import { Component, createSSRApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import Coeliac from '@/Layouts/Coeliac.vue';
import { InertiaPage } from '@/types/Core';
import { getTitle } from '@/helpers';
import AnalyticsTrack from '@/analyticsTrack';

// const setAdOffset = () => {
//   const el: HTMLDivElement | null = document.querySelector(
//     '#adhesion_desktop_wrapper',
//   );
//
//   const height = el?.offsetHeight ?? 0;
//
//   document.documentElement.style.setProperty(
//     '--mv-journey-height',
//     `${height}px`,
//   );
// };
//
// // setAdOffset();
//
// new MutationObserver(setAdOffset).observe(document.body, {
//   childList: true,
//   subtree: true,
// });

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
    const app = createSSRApp({ render: () => h(App, props) });

    const jitComponents: Record<string, { default: Component }> =
      import.meta.glob('./JitComponents/*.vue', { eager: true });

    Object.entries(jitComponents).forEach(([path, module]) => {
      const componentName = path.split('/').pop()?.replace('.vue', '') ?? '';

      const kebabName = componentName
        .replace(/([A-Z])/g, '-$1')
        .replace(/^-/, '')
        .toLowerCase();

      app.component(kebabName, module.default);
    });

    app.use(pinia).use(plugin).mount(el);
  },
});

AnalyticsTrack();
