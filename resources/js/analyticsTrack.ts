import { Page } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { getTitle } from '@/helpers';

export default () => {
  type Event = { detail: { page: Page<{ meta?: { title?: string } }> } };

  if (typeof window === 'undefined' || import.meta.env.VITE_APP_ENV === 'local') {
    return;
  }

  /** @ts-ignore */
  router.on('navigate', (event: Event) => {
    window.gtag('event', 'page_view', {
      page_location: event.detail.page.url,
      page_title: getTitle(event.detail.page.props?.meta?.title),
    });
  });
};
