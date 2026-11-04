<script lang="ts" setup>
import { computed, nextTick, onMounted, Ref, ref, watch } from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
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

type TripLocation = LatLng & { label: string };

type TripRoute = { geometry: string; distance: number; duration: number };

type Trip = {
  key: string;
  points: TripLocation[];
  distance: number;
  route: TripRoute;
};

const props = defineProps<{
  trip?: Trip;
  canEdit?: boolean;
  eateries?: TripEatery[];
}>();

const start: Ref<TripLocation | undefined> = ref(props.trip?.points[0]);
const end: Ref<TripLocation | undefined> = ref(props.trip?.points.at(-1));
const startLookup: Ref<{ setValue: (value: string) => void } | undefined> =
  ref();
const endLookup: Ref<{ setValue: (value: string) => void } | undefined> =
  ref();

const form = useForm<{ points: TripLocation[]; distance: number }>(
  'post',
  '/wheretoeat/trip-planner',
  {
    points: [],
    distance: props.trip?.distance ?? 2,
  },
);

const plannerMode: Ref<'new' | 'edit' | undefined> = ref(
  props.trip ? undefined : 'new',
);

const tripTitle = computed((): string =>
  [props.trip?.points[0], props.trip?.points.at(-1)]
    .map((point) => point?.label.split(',')[0] ?? '')
    .join(' → '),
);

const isEditing = computed((): boolean => plannerMode.value === 'edit');

const submitLabel = computed((): string => {
  if (!isEditing.value) {
    return 'Plan my trip';
  }

  return props.canEdit ? 'Update this trip' : 'Save as my own copy';
});

const openPlanner = (mode: 'new' | 'edit') => {
  const editing = mode === 'edit' && props.trip;

  start.value = editing ? props.trip?.points[0] : undefined;
  end.value = editing ? props.trip?.points.at(-1) : undefined;
  form.distance = editing ? (props.trip?.distance ?? 2) : 2;
  form.clearErrors();
  lookupError.value = false;

  plannerMode.value = mode;
};

const route = computed((): TripRoute | undefined => props.trip?.route);
const eateries = computed((): TripEatery[] => props.eateries ?? []);

const sessionTokens = ref({
  start: crypto.randomUUID(),
  end: crypto.randomUUID(),
});

const lookupEndpoint = (target: 'start' | 'end'): string =>
  `/api/wheretoeat/trip-planner/lookup?sessionToken=${sessionTokens.value[target]}`;

const placeLookup = useHttp<{ sessionToken: string }, LatLng>({
  sessionToken: '',
});

const lookupError = ref(false);

const errorMessage = computed((): string | undefined => {
  if (lookupError.value) {
    return "Sorry, I couldn't find that location...";
  }

  return form.hasErrors
    ? 'Sorry, there was an error planning your trip...'
    : undefined;
});

const mapElement: Ref<HTMLDivElement | undefined> = ref();
const map: Ref<Map | undefined> = ref();
const routeSource = new VectorSource();
const markerSource = new VectorSource();

const distanceOptions = [1, 2, 3, 5, 10].map((miles) => ({
  value: miles,
  label: `Within ${miles} mile${miles > 1 ? 's' : ''} of my route`,
}));

const selectLocation = (
  location: { id: string; label: string },
  target: 'start' | 'end',
) => {
  const lookup = target === 'start' ? startLookup : endLookup;

  lookup.value?.setValue(location.label);
  lookupError.value = false;
  placeLookup.sessionToken = sessionTokens.value[target];

  placeLookup.get(
    `/api/wheretoeat/trip-planner/lookup/${encodeURIComponent(location.id)}`,
    {
      onSuccess: ({ lat, lng }) => {
        const selected = { label: location.label, lat, lng };

        if (target === 'start') {
          start.value = selected;
        } else {
          end.value = selected;
        }

        sessionTokens.value[target] = crypto.randomUUID();
      },
      onHttpException: () => {
        lookupError.value = true;
      },
      onNetworkError: () => {
        lookupError.value = true;
      },
    },
  );
};

const planTrip = () => {
  if (!start.value || !end.value) {
    return;
  }

  form.points = [start.value, end.value];

  const options = {
    onSuccess: () => {
      plannerMode.value = undefined;
    },
  };

  if (isEditing.value && props.canEdit && props.trip) {
    form.submit('patch', `/wheretoeat/trip-planner/${props.trip.key}`, options);

    return;
  }

  form.submit('post', '/wheretoeat/trip-planner', options);
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

watch(
  () => props.trip?.route.geometry,
  () => nextTick(() => drawTrip()),
);

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

  drawTrip();
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
      :display="form.processing"
      background
    />

    <div class="absolute top-0 left-0 z-10 w-[calc(100%-40px)] max-w-lg p-2">
      <div class="flex flex-col gap-2 rounded-md bg-white p-3 shadow-sm">
        <template v-if="trip && route">
          <p
            class="font-semibold"
            v-text="tripTitle"
          />

          <p class="text-sm text-grey-dark">
            {{ metersToMiles(route.distance) }} miles &middot;
            {{ formatDuration(route.duration) }} &middot;
            {{ eateries.length }} places along the way
          </p>
        </template>

        <div class="flex justify-end gap-2">
          <CoeliacButton
            v-if="trip"
            as="button"
            type="button"
            theme="secondary"
            label="Edit trip"
            @click="openPlanner('edit')"
          />

          <CoeliacButton
            as="button"
            type="button"
            theme="primary"
            :label="trip ? 'New trip' : 'Plan a trip'"
            @click="openPlanner('new')"
          />
        </div>
      </div>
    </div>

    <Card
      v-if="route"
      class="absolute bottom-2 left-2 z-10 flex max-h-[40%] w-[calc(100%-1rem)] max-w-md flex-col space-y-2 overflow-y-auto"
    >
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
    :open="plannerMode !== undefined"
    :closeable="!!trip"
    size="relaxed"
    width="w-full"
    no-padding
    @close="plannerMode = undefined"
  >
    <template #header>
      <h3
        class="pl-3 font-semibold lg:text-lg"
        v-text="isEditing ? 'Edit your trip' : 'Plan your trip'"
      />
    </template>

    <form
      class="flex flex-col space-y-3 p-4"
      @submit.prevent="planTrip()"
    >
      <FormLookup
        ref="startLookup"
        :initial-value="start?.label"
        name="start"
        borders
        label="Starting from"
        results-classes="absolute bg-white w-full z-20"
        placeholder="Town, city or postcode..."
        :lookup-endpoint="lookupEndpoint('start')"
      >
        <template #item="location: { id: string; label: string }">
          <div
            class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
            @click="selectLocation(location, 'start')"
            v-text="location.label"
          />
        </template>
      </FormLookup>

      <FormLookup
        ref="endLookup"
        :initial-value="end?.label"
        name="end"
        borders
        label="Going to"
        results-classes="absolute bg-white w-full z-20"
        placeholder="Town, city or postcode..."
        :lookup-endpoint="lookupEndpoint('end')"
      >
        <template #item="location: { id: string; label: string }">
          <div
            class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
            @click="selectLocation(location, 'end')"
            v-text="location.label"
          />
        </template>
      </FormLookup>

      <FormSelect
        v-model="form.distance"
        name="distance"
        borders
        label="How far off your route?"
        help-text="How far from your route should I look for places to eat?"
        :options="distanceOptions"
      />

      <p
        v-if="isEditing && !canEdit"
        class="text-grey-dark"
      >
        This trip was shared with you, so any changes will be saved as your own
        copy.
      </p>

      <CoeliacButton
        theme="primary"
        size="lg"
        as="button"
        type="submit"
        :label="submitLabel"
        bold
        classes="justify-center"
        :loading="form.processing"
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
