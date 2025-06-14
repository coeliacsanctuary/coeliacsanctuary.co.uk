import { Ref, ref } from 'vue';
import { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import useSearchStore from '@/stores/useSearchStore';
import { storeToRefs } from 'pinia';

const latLng = ref<string | null>(null);

export default () => {
  const store = useSearchStore();

  const hasError = ref(false);

  const { data: searchForm } = storeToRefs(store);

  const cancelSearch: Ref<{ cancel: () => void } | undefined> = ref();

  const isSubmitting = ref(false);

  const submitSearch = (
    options: Omit<VisitOptions, 'method' | 'data'> = {},
  ) => {
    hasError.value = false;

    if (searchForm.value.q.length < 3) {
      hasError.value = true;

      return;
    }

    isSubmitting.value = true;

    options = {
      ...options,
      onSuccess: () => {
        isSubmitting.value = false;
      },
    };

    if (searchForm.value.eateries) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          latLng.value = `${position.coords.latitude},${position.coords.longitude}`;

          options = {
            ...options,
            onCancelToken: (cancelToken) => (cancelSearch.value = cancelToken),
            headers: {
              'x-search-location': latLng.value,
            },
          };

          router.get('/search', searchForm.value, options);
        },
        () => {
          router.get('/search', searchForm.value, options);
        },
      );

      return;
    }

    router.get('/search', searchForm.value, {
      ...options,
      preserveState: true,
    });
  };

  return {
    latLng,
    hasError,
    searchForm,
    cancelSearch,
    store,
    isSubmitting,
    submitSearch,
  };
};
