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
import {
  markerStyle,
  tripPointStyle,
  tripStopStyle,
} from '@/support/eating-out/browse/styles';
import { ArrowDownIcon, ArrowUpIcon, XMarkIcon } from '@heroicons/vue/24/outline';
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

type PointRow = {
  key: string;
  location?: TripLocation;
  sessionToken: string;
};

type Trip = {
  key: string;
  points: TripLocation[];
  distance: number;
  stopInterval: number | null;
  route: TripRoute;
};

type TripStop = {
  number: number;
  duration: number;
  distanceAlongRoute: number;
  lat: number;
  lng: number;
};

const props = defineProps<{
  trip?: Trip;
  canEdit?: boolean;
  eateries?: TripEatery[];
  stops?: TripStop[];
}>();

const makeRow = (location?: TripLocation): PointRow => ({
  key: crypto.randomUUID(),
  location,
  sessionToken: crypto.randomUUID(),
});

const rowsFor = (points?: TripLocation[]): PointRow[] =>
  points?.length ? points.map((point) => makeRow(point)) : [makeRow(), makeRow()];

const rows: Ref<PointRow[]> = ref(rowsFor(props.trip?.points));

const lookups: Record<string, { setValue: (value: string) => void } | null> =
  {};

const viaCount = computed((): number => rows.value.length - 2);

const allPointsSelected = computed((): boolean =>
  rows.value.every((row) => row.location !== undefined),
);

const isVia = (index: number): boolean =>
  index > 0 && index < rows.value.length - 1;

const rowLabel = (index: number): string => {
  if (index === 0) {
    return 'Starting from';
  }

  return index === rows.value.length - 1 ? 'Going to' : 'Via';
};

const addStop = () => {
  if (viaCount.value >= 5) {
    return;
  }

  rows.value.splice(rows.value.length - 1, 0, makeRow());
};

const moveStop = (index: number, direction: -1 | 1) => {
  const target = index + direction;

  if (!isVia(index) || !isVia(target)) {
    return;
  }

  [rows.value[index], rows.value[target]] = [rows.value[target], rows.value[index]];
};

const removeStop = (index: number) => {
  if (!isVia(index)) {
    return;
  }

  rows.value.splice(index, 1);
};

const form = useForm<{
  points: TripLocation[];
  distance: number;
  stop_interval: number | '';
}>('post', '/wheretoeat/trip-planner', {
  points: [],
  distance: props.trip?.distance ?? 2,
  stop_interval: props.trip?.stopInterval ?? '',
});

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

  rows.value = rowsFor(editing ? props.trip?.points : undefined);
  form.distance = editing ? (props.trip?.distance ?? 2) : 2;
  form.stop_interval = editing ? (props.trip?.stopInterval ?? '') : '';
  form.clearErrors();
  lookupError.value = false;

  plannerMode.value = mode;
};

const route = computed((): TripRoute | undefined => props.trip?.route);
const eateries = computed((): TripEatery[] => props.eateries ?? []);
const stops = computed((): TripStop[] => props.stops ?? []);

const nearbyEateries = (stop: TripStop): TripEatery[] =>
  eateries.value
    .filter(
      (eatery) => Math.abs(eatery.durationAlongRoute - stop.duration) <= 1800,
    )
    .sort(
      (a, b) =>
        Math.abs(a.durationAlongRoute - stop.duration) -
        Math.abs(b.durationAlongRoute - stop.duration),
    )
    .slice(0, 5);

const legs = computed((): { title: string; eateries: TripEatery[] }[] => {
  const boundaries = [0, ...stops.value.map((stop) => stop.duration), Infinity];

  return boundaries.slice(0, -1).map((from, index) => ({
    title:
      index === 0
        ? 'Before stop 1'
        : index === stops.value.length
          ? 'After the last stop'
          : `Stop ${index} to stop ${index + 1}`,
    eateries: eateries.value.filter(
      (eatery) =>
        eatery.durationAlongRoute >= from &&
        eatery.durationAlongRoute < boundaries[index + 1],
    ),
  }));
});

const stopIntervalOptions = [
  { value: '', label: 'No stops' },
  { value: 60, label: 'Every hour' },
  { value: 90, label: 'Every 1½ hours' },
  { value: 120, label: 'Every 2 hours' },
  { value: 180, label: 'Every 3 hours' },
];

const lookupEndpoint = (row: PointRow): string =>
  `/api/wheretoeat/trip-planner/lookup?sessionToken=${row.sessionToken}`;

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
const pointSource = new VectorSource();
const stopSource = new VectorSource();
const markerSource = new VectorSource();

const distanceOptions = [1, 2, 3, 5, 10].map((miles) => ({
  value: miles,
  label: `Within ${miles} mile${miles > 1 ? 's' : ''} of my route`,
}));

const selectLocation = (
  location: { id: string; label: string },
  row: PointRow,
) => {
  lookups[row.key]?.setValue(location.label);
  lookupError.value = false;
  placeLookup.sessionToken = row.sessionToken;

  placeLookup.get(
    `/api/wheretoeat/trip-planner/lookup/${encodeURIComponent(location.id)}`,
    {
      onSuccess: ({ lat, lng }) => {
        row.location = { label: location.label, lat, lng };
        row.sessionToken = crypto.randomUUID();
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
  if (!allPointsSelected.value) {
    return;
  }

  form.points = rows.value.map((row) => row.location as TripLocation);

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
  pointSource.clear();
  stopSource.clear();
  markerSource.clear();

  const routeFeature = new Polyline().readFeature(route.value.geometry, {
    dataProjection: 'EPSG:4326',
    featureProjection: 'EPSG:3857',
  });

  routeSource.addFeature(routeFeature);

  stopSource.addFeatures(
    stops.value.map(
      (stop) =>
        new Feature({
          geometry: new Point(fromLonLat([stop.lng, stop.lat])),
          number: stop.number,
        }),
    ),
  );

  const points = props.trip?.points ?? [];

  pointSource.addFeatures(
    points.map(
      (point, index) =>
        new Feature({
          geometry: new Point(fromLonLat([point.lng, point.lat])),
          label:
            index === 0
              ? 'S'
              : index === points.length - 1
                ? 'E'
                : index.toString(),
        }),
    ),
  );

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
      new VectorLayer({
        source: stopSource,
        style: (feature) => tripStopStyle(feature.get('number') as number),
      }),
      new VectorLayer({
        source: pointSource,
        style: (feature) => tripPointStyle(feature.get('label') as string),
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
            <template v-if="stops.length">
              {{ stops.length }} suggested stop{{ stops.length > 1 ? 's' : '' }}
              &middot;
            </template>
            <template v-if="trip.points.length > 2">
              via {{ trip.points.length - 2 }}
              stop{{ trip.points.length > 3 ? 's' : '' }} &middot;
            </template>
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
      <ol
        v-if="!stops.length"
        class="list-decimal pl-6"
      >
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

      <template v-else>
        <div
          v-for="stop in stops"
          :key="`stop-${stop.number}`"
          class="flex flex-col"
        >
          <p
            class="font-semibold"
            v-text="`Stop ${stop.number}, about ${formatDuration(stop.duration)} in`"
          />

          <p
            v-if="!nearbyEateries(stop).length"
            class="text-sm text-grey-dark"
          >
            No places within half an hour of this stop.
          </p>

          <ol class="list-decimal pl-6">
            <li
              v-for="eatery in nearbyEateries(stop)"
              :key="eatery.key"
            >
              {{ formatDuration(eatery.durationAlongRoute) }} in,
              {{ metersToMiles(eatery.distanceFromRoute) }} miles off route
              <span v-if="eatery.isNationwideBranch">(nationwide branch)</span>
            </li>
          </ol>
        </div>

        <div
          v-for="leg in legs"
          :key="leg.title"
          class="flex flex-col"
        >
          <p
            class="font-semibold"
            v-text="`${leg.title} (${leg.eateries.length})`"
          />

          <ol class="list-decimal pl-6">
            <li
              v-for="eatery in leg.eateries"
              :key="eatery.key"
            >
              {{ formatDuration(eatery.durationAlongRoute) }} in,
              {{ metersToMiles(eatery.distanceFromRoute) }} miles off route
              <span v-if="eatery.isNationwideBranch">(nationwide branch)</span>
            </li>
          </ol>
        </div>
      </template>
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
      <div
        v-for="(row, index) in rows"
        :key="row.key"
        class="flex items-end gap-2"
      >
        <FormLookup
          :ref="(el) => (lookups[row.key] = el as { setValue: (value: string) => void } | null)"
          :initial-value="row.location?.label"
          :name="`point-${index}`"
          borders
          :label="rowLabel(index)"
          class="flex-1"
          results-classes="absolute bg-white w-full z-20"
          placeholder="Address, town or postcode..."
          :lookup-endpoint="lookupEndpoint(row)"
        >
          <template #item="location: { id: string; label: string }">
            <div
              class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
              @click="selectLocation(location, row)"
              v-text="location.label"
            />
          </template>
        </FormLookup>

        <div
          v-if="isVia(index)"
          class="flex gap-1 pb-1"
        >
          <button
            type="button"
            class="cursor-pointer rounded-md border border-grey-off p-2 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="!isVia(index - 1)"
            @click="moveStop(index, -1)"
          >
            <ArrowUpIcon class="size-4" />
          </button>

          <button
            type="button"
            class="cursor-pointer rounded-md border border-grey-off p-2 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="!isVia(index + 1)"
            @click="moveStop(index, 1)"
          >
            <ArrowDownIcon class="size-4" />
          </button>

          <button
            type="button"
            class="cursor-pointer rounded-md border border-grey-off p-2"
            @click="removeStop(index)"
          >
            <XMarkIcon class="size-4" />
          </button>
        </div>
      </div>

      <div v-if="viaCount < 5">
        <CoeliacButton
          as="button"
          type="button"
          theme="light"
          size="sm"
          label="Add a stop"
          @click="addStop()"
        />
      </div>

      <FormSelect
        v-model="form.distance"
        name="distance"
        borders
        label="How far off your route?"
        help-text="How far from your route should I look for places to eat?"
        :options="distanceOptions"
      />

      <FormSelect
        v-model="form.stop_interval"
        name="stop_interval"
        borders
        label="Suggest stops"
        placeholder=""
        help-text="I'll suggest places to stop and eat at this interval."
        :options="stopIntervalOptions"
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
        :disabled="!allPointsSelected"
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
