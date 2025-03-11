import { ref } from 'vue';
import { InertiaForm } from '@/types/Core';
import { useForm } from 'laravel-precognition-vue-inertia';
import { SearchParams } from '@/types/Search';
import { VisitOptions } from '@inertiajs/core';
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

  const submitSearch = (options: Partial<VisitOptions> = {}) => {
    hasError.value = false;

    if (searchForm.q.length < 3) {
      hasError.value = true;

      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        latLng.value = `${position.coords.latitude},${position.coords.longitude}`;

        options = {
          ...options,
          headers: {
            'x-search-location': latLng.value,
          },
        };

        searchForm.submit(options);
      },
      () => {
        searchForm.submit(options);
      },
    );
  };

  return { latLng, hasError, searchForm, submitSearch };
};
