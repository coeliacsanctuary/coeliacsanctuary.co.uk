import { EateryFilters } from '@/types/EateryTypes';
import { RequestPayload } from '@inertiajs/core';
import { router, usePage } from '@inertiajs/vue3';
import useBrowser from '@/composables/useBrowser';
import useScreensize from '@/composables/useScreensize';

export default () => {
  const { screenIsGreaterThanOrEqualTo } = useScreensize();

  /**
   * The current path carries no query string, so the sort has to be put back
   * explicitly or changing a filter would silently reset it to the default.
   */
  const currentSort = (): string | undefined =>
    (usePage().props as { sort?: { current?: string } }).sort?.current;

  const handleFiltersChanged = ({
    filters,
    preserveState = true,
  }: {
    filters: EateryFilters;
    preserveState?: boolean;
  }) => {
    const categoryFilter = filters.categories
      .filter((filter) => filter.checked)
      .map((filter) => filter.value);

    const venueFilter = filters.venueTypes
      .filter((filter) => filter.checked)
      .map((filter) => filter.value);

    const featureFilter = filters.features
      .filter((filter) => filter.checked)
      .map((filter) => filter.value);

    const params: RequestPayload & {
      sort?: string;
      filter?: { [T in 'category' | 'venueType' | 'feature']?: string };
    } = {};

    const sort = currentSort();

    if (sort) {
      params.sort = sort;
    }

    if (categoryFilter.length || venueFilter.length || featureFilter.length) {
      params.filter = {};

      if (categoryFilter.length) {
        params.filter.category = categoryFilter.join(',');
      }

      if (venueFilter.length) {
        params.filter.venueType = venueFilter.join(',');
      }

      if (featureFilter.length) {
        params.filter.feature = featureFilter.join(',');
      }
    }

    const lastScroll = window.scrollY;

    router.get(useBrowser().currentPath(), params, {
      preserveState: screenIsGreaterThanOrEqualTo('xmd')
        ? false
        : preserveState,
      preserveScroll: true,
      onFinish: () => {
        // This avoids race conditions with hydration
        requestAnimationFrame(() => {
          window.scrollTo(0, lastScroll);
        });
      },
    });
  };

  return { handleFiltersChanged };
};
