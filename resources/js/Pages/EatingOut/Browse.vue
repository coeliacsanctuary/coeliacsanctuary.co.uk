<script lang="ts" setup>
import { Component, computed, ComputedRef, onMounted, Ref, ref } from 'vue';
import SearchMap from '@/Components/PageSpecific/EatingOut/Browse/SearchMap.vue';
import axios, { AxiosResponse } from 'axios';
import {
  EateryBrowseResource,
  EateryFilterItem,
  EateryFilterKeys,
  EateryFilters,
  LatLng,
} from '@/types/EateryTypes';
import { DataResponse } from '@/types/GenericTypes';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import { FeatureLike } from 'ol/Feature';
import Loader from '@/Components/Loader.vue';
import CoeliacCompact from '@/Layouts/CoeliacCompact.vue';
import { router, usePage } from '@inertiajs/vue3';
import { DefaultProps } from '@/types/DefaultProps';
import 'ol/ol.css';
import PlaceDetails from '@/Components/PageSpecific/EatingOut/Browse/PlaceDetails.vue';
import useBrowser from '@/composables/useBrowser';
import RecommendAPlaceCta from '@/Components/PageSpecific/EatingOut/Browse/RecommendAPlaceCta.vue';
import FilterMap from '@/Components/PageSpecific/EatingOut/Browse/FilterMap.vue';
import { Pixel } from 'ol/pixel';
import { UrlParameters } from '@/types/EatingOutBrowseTypes';
import Map from '@/support/eating-out/browse/map';
import Markers from '@/support/eating-out/browse/markers';
import eventBus from '@/eventBus';

type FilterKeys = 'category' | 'venueType' | 'feature';
type UrlFilter = { [T in FilterKeys]?: string };

defineOptions({
  layout: CoeliacCompact as Component,
});

const isLoading = ref(true);

const wrapper: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;

const mapFilters: Ref<Partial<EateryFilters>> = ref({});

const processedUrl: Ref<UrlParameters> = ref({});

const showPlaceDetails: Ref<false | { id: number; branchId?: number }> =
  ref(false);

const { processMapMarkers, zoomIntoCluster, rawMarkerSource } = Markers();

const {
  createMap,
  getZoom,
  getExtent,
  getViewableRadius,
  getLatLng,
  navigateTo,
} = Map(processedUrl, rawMarkerSource as Ref<VectorSource>);

const cancelGetPlaces: Ref<AbortController | undefined> = ref(undefined);

const filtersForUrl: ComputedRef<{ filter: UrlFilter }> = computed(() => {
  const filter: UrlFilter = {};

  if (processedUrl.value.categories) {
    filter.category = processedUrl.value.categories;
  }

  if (processedUrl.value.venueTypes) {
    filter.venueType = processedUrl.value.venueTypes;
  }

  if (processedUrl.value.features) {
    filter.feature = processedUrl.value.features;
  }

  return { filter };
});

const filtersForFilterBar: ComputedRef<
  Partial<{ [T in EateryFilterKeys]: string[] }>
> = computed(() => {
  const rtr: Partial<{ [T in EateryFilterKeys]: string[] }> = {};

  const keys: EateryFilterKeys[] = ['categories', 'venueTypes', 'features'];

  keys.forEach((key) => {
    if (
      processedUrl.value[key] === undefined ||
      processedUrl.value[key] === ''
    ) {
      return;
    }

    const url: string = processedUrl.value[key];

    rtr[key] = url.split(',');
  });

  return rtr;
});

const getPlaces = async (): Promise<EateryBrowseResource[]> => {
  cancelGetPlaces.value = new AbortController();

  const response: AxiosResponse<DataResponse<EateryBrowseResource[]>> =
    await axios.get('/api/wheretoeat/browse', {
      signal: cancelGetPlaces.value ? cancelGetPlaces.value.signal : undefined,
      params: {
        ...getLatLng(),
        radius: getViewableRadius(),
        ...filtersForUrl.value,
      },
    });

  cancelGetPlaces.value = undefined;

  return response.data.data;
};

const populateMap = (): void => {
  void getPlaces()
    .then((eateries: EateryBrowseResource[]) => {
      processMapMarkers(eateries, getZoom(), getExtent());
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const getValueForFilter = (filter: EateryFilterKeys): string => {
  const filters: EateryFilterItem[] = mapFilters.value[
    filter
  ] as EateryFilterItem[];

  return filters
    .filter((item) => item.checked)
    .map((item) => item.value)
    .join(',');
};

const updateUrl = (latLng?: LatLng, zoom?: number) => {
  if (!latLng) {
    latLng = getLatLng();
  }

  if (!zoom) {
    zoom = getZoom();
  }

  const paths = [`${latLng.lat},${latLng.lng}`, Math.round(zoom)];

  const queryStrings: { [T in EateryFilterKeys]: string | undefined } = {
    categories: mapFilters.value?.categories
      ? getValueForFilter('categories')
      : undefined,
    venueTypes: mapFilters.value?.venueTypes
      ? getValueForFilter('venueTypes')
      : undefined,
    features: mapFilters.value?.features
      ? getValueForFilter('features')
      : undefined,
  };

  const { baseUrl } = usePage<DefaultProps>().props.meta;

  const url = new URL(`${baseUrl}/wheretoeat/browse/${paths.join('/')}`);

  Object.keys(queryStrings).forEach((key) => {
    const value: string | undefined = queryStrings[key as keyof EateryFilters];

    if (!value) {
      return;
    }

    url.searchParams.set(key, value);
  });

  router.get(url.toString(), undefined, {
    replace: true,
    preserveScroll: true,
    preserveState: true,
  });
};

const handleFiltersChange = ({ filters }: { filters: EateryFilters }): void => {
  mapFilters.value = filters;

  const keys: EateryFilterKeys[] = ['categories', 'venueTypes', 'features'];

  keys.forEach((key) => {
    processedUrl.value = {
      ...processedUrl.value,
      [key]: getValueForFilter(key),
    };
  });

  handleMapMove();
};

const parseUrl = () => {
  const url = new URL(useBrowser().currentUrl());

  let paths = url.pathname.replace('/wheretoeat/browse', '');
  const queryStrings = url.searchParams;

  if (paths.charAt(0) === '/') {
    paths = paths.replace('/', '');
  }

  const [latLng, zoom] = paths.split('/');

  processedUrl.value.latLng = latLng;
  processedUrl.value.zoom = zoom;

  const keys: EateryFilterKeys[] = ['categories', 'venueTypes', 'features'];

  keys.forEach((key) => {
    if (queryStrings.has(key)) {
      processedUrl.value[key] = queryStrings.get(key) as string;
    }
  });
};

const handleMapFeatureClick = (eatery: FeatureLike) => {
  const eateryId: string = eatery.get('id') as string;
  const splitId = eateryId.split('-');

  showPlaceDetails.value = {
    id: parseInt(splitId[0], 10),
    branchId: splitId[1] ? parseInt(splitId[1], 10) : undefined,
  };
};

const handleMapMove = () => {
  updateUrl();
  populateMap();
};

const registerListeners = () => {
  eventBus.$on<boolean>(
    'map-loading',
    (loading) => (isLoading.value = loading),
  );

  eventBus.$on('map-moved', handleMapMove);

  eventBus.$on<{ pixel: Pixel; markerLayer: VectorLayer<VectorSource> }>(
    'cluster-clicked',
    zoomIntoCluster,
  );

  eventBus.$on<FeatureLike>('clicked-feature', handleMapFeatureClick);
};

onMounted(() => {
  parseUrl();
  createMap();
  populateMap();
  registerListeners();
});
</script>

<template>
  <div
    ref="wrapper"
    class="relative -mb-3 flex max-h-full min-h-[500px] flex-1 overflow-hidden"
  >
    <Loader
      class="z-50"
      size="size-16"
      width="border-8"
      :display="isLoading"
      background
    />

    <SearchMap
      @loading="isLoading = true"
      @end-loading="isLoading = false"
      @navigate-to="navigateTo($event)"
    />

    <RecommendAPlaceCta />

    <FilterMap
      :set-filters="filtersForFilterBar"
      @filters-updated="handleFiltersChange"
    />

    <PlaceDetails
      :show="showPlaceDetails !== false"
      :place-id="showPlaceDetails ? showPlaceDetails.id : 0"
      :branch-id="showPlaceDetails ? showPlaceDetails.branchId : undefined"
      @close="showPlaceDetails = false"
    />

    <div
      id="map"
      class="w-full"
    />
  </div>
</template>

<style>
.ol-zoom {
  left: auto;
  right: 0.5em;
  z-index: 50;
}

.ol-zoom button {
  width: 1.75rem;
  height: 1.75rem;
  font-size: 1.75rem;
  font-weight: 100;
}
</style>
