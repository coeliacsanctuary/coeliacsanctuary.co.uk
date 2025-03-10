<script lang="ts" setup>
import useScreensize from '@/composables/useScreensize';
import {
  computed,
  ComputedRef,
  DefineComponent,
  onMounted,
  Ref,
  ref,
} from 'vue';
import { fromLonLat, toLonLat, transformExtent } from 'ol/proj';
import { Coordinate } from 'ol/coordinate';
import { Cluster, OSM } from 'ol/source';
import Map from 'ol/Map';
import TileLayer from 'ol/layer/Tile';
import { Feature, MapBrowserEvent, View } from 'ol';
import { getDistance } from 'ol/sphere';
import SearchMap from '@/Components/PageSpecific/EatingOut/Browse/SearchMap.vue';
import FilterMap from '@/Components/PageSpecific/EatingOut/Browse/FilterMap.vue';
import axios, { AxiosResponse } from 'axios';
import {
  EateryBrowseResource,
  EateryFilterItem,
  EateryFilterKeys,
  EateryFilters,
  LatLng,
} from '@/types/EateryTypes';
import { DataResponse } from '@/types/GenericTypes';
import BaseLayer from 'ol/layer/Base';
import { Point } from 'ol/geom';
import { Fill, Icon, Stroke, Style, Text } from 'ol/style';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import CircleStyle from 'ol/style/Circle';
import { FeatureLike } from 'ol/Feature';
import Loader from '@/Components/Loader.vue';
import CoeliacCompact from '@/Layouts/CoeliacCompact.vue';
import { boundingExtent } from 'ol/extent';
import { Pixel } from 'ol/pixel';
import { usePage } from '@inertiajs/vue3';
import { DefaultProps } from '@/types/DefaultProps';
import 'ol/ol.css';
import PlaceDetails from '@/Components/PageSpecific/EatingOut/Browse/PlaceDetails.vue';
import useBrowser from '@/composables/useBrowser';

type FilterKeys = 'category' | 'venueType' | 'feature';
type UrlFilter = { [T in FilterKeys]?: string };

defineOptions({
  layout: CoeliacCompact as DefineComponent,
});

const isLoading = ref(true);

const wrapper: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;

const mapFilters: Ref<Partial<EateryFilters>> = ref({});

const map: Ref<Map> = ref() as Ref<Map>;
const view: Ref<View> = ref() as Ref<View>;

const processedUrl: Ref<{
  latLng?: string;
  zoom?: string;
  categories?: string;
  venueTypes?: string;
  features?: string;
}> = ref({});

const showPlaceDetails: Ref<false | { id: number; branchId?: number }> =
  ref(false);

const { currentBreakpoint, screenIsGreaterThanOrEqualTo } = useScreensize();

const initialLatLng = computed((): Coordinate => {
  let latLng: [number, number] = [54.093409, -2.89479];

  if (processedUrl.value.latLng) {
    latLng = processedUrl.value.latLng
      .split(',')
      .map((str) => parseFloat(str)) as [number, number];
  }

  return fromLonLat(latLng.reverse());
});

const initialZoom = computed((): number => {
  if (processedUrl.value.zoom) {
    return parseFloat(processedUrl.value.zoom);
  }

  switch (currentBreakpoint()) {
    case 'sm':
    case 'xmd':
    case 'md':
    case 'lg':
    case 'xl':
    case '2xl':
      return 6;
    case 'xs':
    case 'xxs':
    default:
      return 5;
  }
});

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
    if (processedUrl.value[key] === undefined) {
      return;
    }

    const url: string = processedUrl.value[key];

    rtr[key] = url.split(',');
  });

  return rtr;
});

const getViewableRadius = (): number => {
  const latLng = transformExtent(
    map.value.getView().calculateExtent(map.value.getSize()),
    'EPSG:3857',
    'EPSG:4326',
  );

  return getDistance([latLng[0], latLng[1]], [latLng[2], latLng[3]]) / 1609;
};

const getLatLng = (): LatLng => {
  const latLng = toLonLat(
    map.value.getView().getCenter() as Coordinate,
  ).reverse();

  return {
    lat: latLng[0],
    lng: latLng[1],
  };
};

const getPlaces = async (): Promise<EateryBrowseResource[]> => {
  const response: AxiosResponse<DataResponse<EateryBrowseResource[]>> =
    await axios.get('/api/wheretoeat/browse', {
      params: {
        ...getLatLng(),
        radius: getViewableRadius(),
        ...filtersForUrl.value,
      },
    });

  return response.data.data;
};

const getMarkersLayer = (): VectorLayer<VectorSource> | undefined => {
  let rtr;

  map.value.getLayers().forEach((layer: BaseLayer) => {
    if (layer.get('name') !== undefined && layer.get('name') === 'markers') {
      rtr = layer;
    }
  });

  return rtr;
};

const clearMarkers = (): void => {
  map.value.removeLayer(getMarkersLayer() as VectorLayer<VectorSource>);
  map.value.removeLayer(getMarkersLayer() as VectorLayer<VectorSource>);
  map.value.removeLayer(getMarkersLayer() as VectorLayer<VectorSource>);
  map.value.removeLayer(getMarkersLayer() as VectorLayer<VectorSource>);
  map.value.removeLayer(getMarkersLayer() as VectorLayer<VectorSource>);
};

const getEateryLatLng = (eatery: EateryBrowseResource): Coordinate =>
  [eatery.location.lng, eatery.location.lat] as Coordinate;

const markerStyle = (color: string): Style =>
  new Style({
    image: new Icon({
      size: [50, 50],
      src: '/images/svg/marker.svg',
      color,
    }),
  });

const getZoom = (): number => map.value.getView().getZoom() as number;

const clusterStyle = (feature: FeatureLike) => {
  const size = (<Feature[]>feature.get('features')).length;

  return new Style({
    image: new CircleStyle({
      radius: 20,
      stroke: new Stroke({
        color: '#000',
      }),
      fill: new Fill({
        color: '#ecd14a',
      }),
    }),
    text: new Text({
      text: size.toString(),
      fill: new Fill({
        color: '#000',
      }),
      scale: 2,
    }),
  });
};

const zoomLimit = (): number => {
  if (screenIsGreaterThanOrEqualTo('2xl')) {
    return 10;
  }

  if (screenIsGreaterThanOrEqualTo('lg')) {
    return 11;
  }

  return 13;
};

const createMaMarkerLayer = (
  markers: Feature[],
): VectorLayer<VectorSource> | VectorLayer<Cluster> => {
  if (getZoom() < zoomLimit()) {
    return new VectorLayer({
      properties: {
        name: 'markers',
      },
      source: new Cluster({
        distance: 60,
        minDistance: 30,
        source: new VectorSource({
          features: markers,
        }),
      }),
      style: (feature) => clusterStyle(feature),
    });
  }

  return new VectorLayer({
    properties: {
      name: 'markers',
    },
    source: new VectorSource({
      features: markers,
    }),
  });
};

const populateMap = (): void => {
  isLoading.value = true;
  clearMarkers();

  void getPlaces()
    .then((eateries: EateryBrowseResource[]) => {
      const markers = eateries
        .map(
          (eatery) =>
            new Feature({
              id: eatery.key,
              geometry: new Point(fromLonLat(getEateryLatLng(eatery))),
              color: eatery.color,
            }),
        )
        .map((eatery: Feature) => {
          eatery.setStyle(markerStyle(eatery.getProperties().color as string));

          return eatery;
        });

      map.value.addLayer(createMaMarkerLayer(markers));
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const zoomIntoCluster = (pixel: Pixel) => {
  getMarkersLayer()
    ?.getFeatures(pixel)
    .then((clickedFeatures) => {
      if (clickedFeatures.length > 0) {
        // Get clustered Coordinates
        const features: Feature[] = clickedFeatures[0].get(
          'features',
        ) as Feature[];

        if (features.length > 0) {
          const extent = boundingExtent(
            features.map((r: Feature) =>
              (<Point>r.get('geometry')).getCoordinates(),
            ),
          );

          map.value
            .getView()
            .fit(extent, { duration: 500, padding: [50, 50, 50, 50] });

          setTimeout(() => {
            if (getZoom() >= 18) {
              map.value.getView().setZoom(18);
            }
          }, 600);
        }
      }
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

  useBrowser().replaceHistory(url, null);
};

const handleMapClick = (event: MapBrowserEvent<MouseEvent>) => {
  try {
    void getMarkersLayer()
      ?.getFeatures(event.pixel)
      .then((feature) => {
        if (!feature.length) {
          return;
        }

        if (getZoom() < zoomLimit()) {
          // cluster view
          zoomIntoCluster(event.pixel);

          return;
        }

        const eatery: FeatureLike = feature[0];
        const eateryId: string = eatery.get('id') as string;
        const splitId = eateryId.split('-');

        showPlaceDetails.value = {
          id: parseInt(splitId[0], 10),
          branchId: splitId[1] ? parseInt(splitId[1], 10) : undefined,
        };
      });
  } catch (e) {
    //
  }
};

const handleMapMove = () => {
  updateUrl();
  populateMap();
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

const createMap = () => {
  view.value = new View({
    center: initialLatLng.value,
    zoom: initialZoom.value,
    enableRotation: false,
  });

  map.value = new Map({
    layers: [
      new TileLayer({
        source: new OSM(),
      }),
    ],
    target: 'map',
    view: view.value,
  });

  map.value.on('moveend', handleMapMove);

  map.value.on('click', handleMapClick);

  map.value.on('pointermove', (event: MapBrowserEvent<MouseEvent>) => {
    if (event.dragging) {
      return;
    }

    map.value.getTargetElement().style.cursor = map.value.hasFeatureAtPixel(
      map.value.getEventPixel(event.originalEvent),
    )
      ? 'pointer'
      : '';
  });
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

const navigateTo = (latLng: LatLng): void => {
  const coordinates = fromLonLat([latLng.lng, latLng.lat]);

  view.value.animate({
    center: coordinates,
    duration: 1000,
    zoom: 13,
  });
};

onMounted(() => {
  parseUrl();

  createMap();

  populateMap();
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

    <FilterMap
      :set-filters="filtersForFilterBar"
      @filters-updated="handleFiltersChange($event)"
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
