<script lang="ts" setup>
import { nextTick, onMounted, Ref, ref } from 'vue';
import { useHttp } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Loader from '@/Components/Loader.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FormLookup from '@/Components/Forms/FormLookup.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { EateryBrowseResource, LatLng } from '@/types/EateryTypes';
import { Feature, View } from 'ol';
import Map from 'ol/Map';
import TileLayer from 'ol/layer/Tile';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import { OSM } from 'ol/source';
import { fromLonLat } from 'ol/proj';
import { Point } from 'ol/geom';
import Polyline from 'ol/format/Polyline';
import { Stroke, Style } from 'ol/style';
import { markerStyle } from '@/support/eating-out/browse/styles';
import 'ol/ol.css';
import CoeliacCompact from '@/Layouts/CoeliacCompact.vue';

defineOptions({
  layout: CoeliacCompact,
});

type TripEatery = EateryBrowseResource & {
  distanceAlongRoute: number;
  distanceFromRoute: number;
  durationAlongRoute: number;
};

type TripLocation = LatLng & { id: string; label: string };

type TripRoute = { geometry: string; distance: number; duration: number };

const start: Ref<TripLocation | undefined> = ref();
const end: Ref<TripLocation | undefined> = ref();
const startSearch = ref('');
const endSearch = ref('');
const startLookup: Ref<{ setValue: (value: string) => void } | undefined> =
  ref();
const endLookup: Ref<{ setValue: (value: string) => void } | undefined> =
  ref();

const http = useHttp<
  { points: LatLng[]; distance: number },
  { route: TripRoute; eateries: TripEatery[] }
>({
  points: [],
  distance: 2,
});

const errorMessage: Ref<string | undefined> = ref();
const showPlanner = ref(true);

const route: Ref<TripRoute | undefined> = ref();
const eateries: Ref<TripEatery[]> = ref([]);

const mapElement: Ref<HTMLDivElement | undefined> = ref();
const map: Ref<Map | undefined> = ref();
const routeSource = new VectorSource();
const markerSource = new VectorSource();

const distanceOptions = [1, 2, 3, 5, 10].map((miles) => ({
  value: miles,
  label: `Within ${miles} mile${miles > 1 ? 's' : ''} of my route`,
}));

const selectLocation = (location: TripLocation, target: 'start' | 'end') => {
  if (target === 'start') {
    start.value = location;
    startLookup.value?.setValue(location.label);

    return;
  }

  end.value = location;
  endLookup.value?.setValue(location.label);
};

const planTrip = () => {
  if (!start.value || !end.value) {
    return;
  }

  errorMessage.value = undefined;

  http.points = [start.value, end.value].map(({ lat, lng }) => ({ lat, lng }));

  const showError = () => {
    errorMessage.value = 'Sorry, there was an error planning your trip...';
  };

  http.post('/api/wheretoeat/trip-planner', {
    onSuccess: (response) => {
      route.value = response.route;
      eateries.value = response.eateries;
      showPlanner.value = false;

      nextTick(() => drawTrip());
    },
    onError: showError,
    onHttpException: showError,
    onNetworkError: showError,
  });
};

const drawTrip = () => {
  if (!map.value || !route.value) {
    return;
  }

  routeSource.clear();
  markerSource.clear();

  const routeFeature = new Polyline().readFeature(route.value.geometry, {
    dataProjection: 'EPSG:4326',
    featureProjection: 'EPSG:3857',
  });

  routeSource.addFeature(routeFeature);

  markerSource.addFeatures(
    eateries.value.map(
      (eatery) =>
        new Feature({
          geometry: new Point(
            fromLonLat([eatery.location.lng, eatery.location.lat]),
          ),
          typeId: eatery.typeId,
          venueTypeId: eatery.venueTypeId,
        }),
    ),
  );

  map.value.getView().fit(routeSource.getExtent(), { padding: [30, 30, 30, 30] });
};

const metersToMiles = (meters: number): string =>
  (meters / 1609.344).toFixed(1);

const formatDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.round((seconds % 3600) / 60);

  return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
};

onMounted(() => {
  map.value = new Map({
    target: mapElement.value,
    layers: [
      new TileLayer({ source: new OSM() }),
      new VectorLayer({
        source: routeSource,
        style: new Style({ stroke: new Stroke({ color: '#29719f', width: 5 }) }),
      }),
      new VectorLayer({
        source: markerSource,
        style: (feature) =>
          markerStyle(
            feature.get('typeId') as number,
            feature.get('venueTypeId') as number | null,
          ),
      }),
    ],
    view: new View({
      center: fromLonLat([-2.89479, 54.093409]),
      zoom: 5,
      enableRotation: false,
    }),
  });
});
</script>

<template>
  <div
    class="map-container relative -mb-3 flex max-h-full min-h-[500px] flex-1 overflow-hidden"
  >
    <Loader
      class="z-50"
      size="size-16"
      width="border-8"
      :display="http.processing"
      background
    />

    <div class="absolute top-0 left-0 z-10 p-2">
      <CoeliacButton
        as="button"
        type="button"
        :label="route ? 'Plan a new trip' : 'Plan a trip'"
        @click="showPlanner = true"
      />
    </div>

    <Card
      v-if="route"
      class="absolute bottom-2 left-2 z-10 flex max-h-[40%] w-[calc(100%-1rem)] max-w-md flex-col space-y-2 overflow-y-auto"
    >
      <p class="font-semibold">
        {{ metersToMiles(route.distance) }} miles,
        {{ formatDuration(route.duration) }} -
        {{ eateries.length }} places along the way
      </p>

      <ol class="list-decimal pl-6">
        <li
          v-for="eatery in eateries"
          :key="eatery.key"
        >
          {{ formatDuration(eatery.durationAlongRoute) }} in,
          {{ metersToMiles(eatery.distanceAlongRoute) }} miles along,
          {{ metersToMiles(eatery.distanceFromRoute) }} miles off route
          <span v-if="eatery.isNationwideBranch">(nationwide branch)</span>
        </li>
      </ol>
    </Card>

    <div
      ref="mapElement"
      class="w-full"
    />
  </div>

  <Modal
    :open="showPlanner"
    :closeable="!!route"
    size="relaxed"
    width="w-full"
    no-padding
    @close="showPlanner = false"
  >
    <template #header>
      <h3 class="pl-3 font-semibold lg:text-lg">Plan your trip</h3>
    </template>

    <form
      class="flex flex-col space-y-3 p-4"
      @submit.prevent="planTrip()"
    >
      <FormLookup
        ref="startLookup"
        v-model="startSearch"
        name="start"
        borders
        label="Starting from"
        results-classes="absolute bg-white w-full z-20"
        placeholder="Town, city or postcode..."
        lookup-endpoint="/api/wheretoeat/trip-planner/lookup"
      >
        <template #item="location: TripLocation">
          <div
            class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
            @click="selectLocation(location, 'start')"
            v-text="location.label"
          />
        </template>
      </FormLookup>

      <FormLookup
        ref="endLookup"
        v-model="endSearch"
        name="end"
        borders
        label="Going to"
        results-classes="absolute bg-white w-full z-20"
        placeholder="Town, city or postcode..."
        lookup-endpoint="/api/wheretoeat/trip-planner/lookup"
      >
        <template #item="location: TripLocation">
          <div
            class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
            @click="selectLocation(location, 'end')"
            v-text="location.label"
          />
        </template>
      </FormLookup>

      <FormSelect
        v-model="http.distance"
        name="distance"
        borders
        label="How far off your route?"
        help-text="How far from your route should I look for places to eat?"
        :options="distanceOptions"
      />

      <CoeliacButton
        theme="primary"
        size="lg"
        as="button"
        type="submit"
        label="Plan my trip"
        bold
        classes="justify-center"
        :loading="http.processing"
        :disabled="!start || !end"
      />

      <p
        v-if="errorMessage"
        class="text-red"
        v-text="errorMessage"
      />
    </form>
  </Modal>
</template>

<style>
.map-container .adsbygoogle,
.map-container .google-auto-placed {
  display: none !important;
}

.ol-zoom {
  left: auto;
  right: 0.5em;
  z-index: 50;
}
</style>
