import { computed, ComputedRef, Ref, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import useIntersect from '@/composables/useIntersect';
import { PaginatedCollection } from '@/types/GenericTypes';
import { VisitOptions } from '@inertiajs/core/types/types';

export default <T>(propName: string, landmark: Ref<Element> | null = null) => {
  const value: () => PaginatedCollection<T> = () =>
    usePage().props[propName] as PaginatedCollection<T>;

  const items: Ref<T[]> = ref(value().data) as Ref<T[]>;

  const initialUrl = ref(usePage().url);

  const canLoadMoreItems: ComputedRef<boolean> = computed(
    () => value().next_page_url !== null,
  );

  const pause: Ref<boolean> = ref(false);

  const requestOptions: Ref<Partial<VisitOptions>> = ref({});

  const refreshUrl = (url: string) => {
    initialUrl.value = url;
  };

  const loadMoreItems = (): void => {
    if (pause.value || !canLoadMoreItems.value) {
      return;
    }

    let data = {};

    if (requestOptions.value.data) {
      data = requestOptions.value.data;

      delete requestOptions.value.data;
    }

    const options: VisitOptions = {
      ...requestOptions.value,
      preserveState: true,
      preserveScroll: true,
      replace: true,
      /** @ts-ignore */
      preserveUrl: true,
      only: [propName],
      onSuccess: () => {
        items.value = [...items.value, ...value().data];
      },
    };

    /** @ts-ignore */
    router.get(<string>value().next_page_url, data, options);
  };

  if (landmark !== null) {
    useIntersect(landmark, loadMoreItems, {
      rootMargin: '0px 0px 150px 0px',
    });
  }

  return {
    items,
    pause,
    requestOptions,
    refreshUrl,
    loadMoreItems,
    reset: (): void => {
      items.value = value().data;
    },
    canLoadMoreItems,
  };
};
