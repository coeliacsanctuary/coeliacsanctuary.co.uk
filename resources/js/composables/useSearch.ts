import { Ref, ref } from 'vue';
import { InertiaForm } from '@/types/Core';
import { useForm } from 'laravel-precognition-vue-inertia';
import { SearchParams } from '@/types/Search';
import { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';

const latLng = ref<string | null>(null);

export default () => {
  const hasError = ref(false);

  const searchForm: InertiaForm<SearchParams> = useForm('get', '/search', {
    q: '',
    blogs: true,
    recipes: true,
    eateries: false,
    shop: true,
  }) as InertiaForm<SearchParams>;

  const cancelSearch: Ref<{ cancel: () => void } | undefined> = ref();

  const submitSearch = (
    options: Omit<VisitOptions, 'method' | 'data'> = {},
  ) => {
    hasError.value = false;

    if (searchForm.q.length < 3) {
      hasError.value = true;

      return;
    }

    if (searchForm.eateries) {
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

          router.get('/search', searchForm.data(), options);
        },
        () => {
          router.get('/search', searchForm.data(), options);
        },
      );

      return;
    }

    router.get('/search', searchForm.data(), {
      ...options,
      preserveState: true,
    });
  };

  return { latLng, hasError, searchForm, cancelSearch, submitSearch };
};
