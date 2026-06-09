import './bootstrap';
import '../css/app.css';
import { Component, createApp } from 'vue';

const app = createApp({});

const jitComponents: Record<string, { default: Component }> = import.meta.glob(
  './JitComponents/*.vue',
  { eager: true },
);

Object.entries(jitComponents).forEach(([path, module]) => {
  const componentName = path.split('/').pop()?.replace('.vue', '') ?? '';

  const kebabName = componentName
    .replace(/([A-Z])/g, '-$1')
    .replace(/^-/, '')
    .toLowerCase();

  app.component(kebabName, module.default);
});

app.mount('#app');
