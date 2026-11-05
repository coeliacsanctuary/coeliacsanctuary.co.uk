<script lang="ts" setup>
import {
  computed,
  nextTick,
  onMounted,
  Ref,
  ref,
  shallowRef,
  ShallowRef,
  watch,
} from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { router, useHttp, usePage } from '@inertiajs/vue3';
import Loader from '@/Components/Loader.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FormLookup from '@/Components/Forms/FormLookup.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import FilterMap from '@/Components/PageSpecific/EatingOut/Browse/FilterMap.vue';
import TripPanel from '@/Components/PageSpecific/EatingOut/TripPlanner/TripPanel.vue';
import {
  EateryFilterItem,
  EateryFilterKeys,
  EateryFilters,
  LatLng,
} from '@/types/EateryTypes';
import { Feature, Overlay, View } from 'ol';
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
import {
  Trip,
  TripEatery,
  TripLocation,
  TripRoute,
  TripStop,
} from '@/types/TripPlannerTypes';

defineOptions({
  layout: CoeliacCompact,
});

type PointRow = {
  key: string;
  location?: TripLocation;
  sessionToken: string;
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

const selectedEatery: Ref<TripEatery | undefined> = ref();
const hoveredEatery: Ref<TripEatery | undefined> = ref();
const hoveredFromMap = ref(false);

const hoverFromList = (eatery?: TripEatery) => {
  hoveredFromMap.value = false;
  hoveredEatery.value = eatery;
};
const pulseElement: Ref<HTMLDivElement | undefined> = ref();
const pulseOverlay = new Overlay({
  positioning: 'center-center',
  stopEvent: false,
});

watch(hoveredEatery, (eatery) => {
  pulseOverlay.setPosition(
    eatery ? fromLonLat([eatery.location.lng, eatery.location.lat]) : undefined,
  );

  markerLayer.changed();
});

const tripPanel: Ref<{ $el: HTMLElement } | undefined> = ref();

const panelCoversMap = (): boolean => {
  const mapBounds = mapElement.value?.getBoundingClientRect();
  const panelBounds = tripPanel.value?.$el.getBoundingClientRect();

  if (!mapBounds || !panelBounds) {
    return false;
  }

  return panelBounds.left < mapBounds.right && panelBounds.right > mapBounds.left;
};

const panToEatery = (eatery: TripEatery) => {
  const view = map.value?.getView();
  const size = map.value?.getSize();
  const mapBounds = mapElement.value?.getBoundingClientRect();
  const panelBounds = tripPanel.value?.$el.getBoundingClientRect();
  const resolution = view?.getResolution();

  if (!view || !size || !mapBounds || !panelBounds || !resolution) {
    return;
  }

  const coordinate = fromLonLat([eatery.location.lng, eatery.location.lat]);
  const visibleHeight = panelCoversMap()
    ? Math.max(panelBounds.top - mapBounds.top, 0)
    : size[1];

  view.animate({
    center: [
      coordinate[0],
      coordinate[1] - (size[1] / 2 - visibleHeight / 2) * resolution,
    ],
    duration: 300,
  });
};

const selectEatery = (eatery: TripEatery) => {
  selectedEatery.value = eatery;

  if (!panelCoversMap()) {
    panToEatery(eatery);

    return;
  }

  window.setTimeout(() => panToEatery(eatery), 320);
};

const filterKeys: EateryFilterKeys[] = ['categories', 'venueTypes', 'features'];

const page = usePage();

const currentFilters = computed(
  (): Partial<{ [T in EateryFilterKeys]: string[] }> => {
    const query = new URLSearchParams(page.url.split('?')[1] ?? '');
    const filters: Partial<{ [T in EateryFilterKeys]: string[] }> = {};

    filterKeys.forEach((key) => {
      const value = query.get(key);

      if (value) {
        filters[key] = value.split(',');
      }
    });

    return filters;
  },
);

const handleFiltersChange = ({ filters }: { filters: EateryFilters }) => {
  const query: Partial<{ [T in EateryFilterKeys]: string }> = {};

  filterKeys.forEach((key) => {
    const value = (filters[key] as EateryFilterItem[])
      .filter((item) => item.checked)
      .map((item) => item.value)
      .join(',');

    if (value) {
      query[key] = value;
    }
  });

  router.get(page.url.split('?')[0], query, {
    only: ['eateries'],
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

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
const map: ShallowRef<Map | undefined> = shallowRef();
const routeSource = new VectorSource();
const pointSource = new VectorSource();
const stopSource = new VectorSource();
const markerSource = new VectorSource();
const markerLayer = new VectorLayer({
  source: markerSource,
  style: (feature) =>
    markerStyle(
      feature.get('typeId') as number,
      feature.get('venueTypeId') as number | null,
      (feature.get('eatery') as TripEatery).key === hoveredEatery.value?.key,
    ),
});

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
  markerSource.clear();

  const routeFeature = new Polyline().readFeature(route.value.geometry, {
    dataProjection: 'EPSG:4326',
    featureProjection: 'EPSG:3857',
  });

  routeSource.addFeature(routeFeature);

  drawStops();

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

  drawEateries();

  map.value.getView().fit(routeSource.getExtent(), { padding: [30, 30, 30, 30] });
};

const drawStops = () => {
  stopSource.clear();

  stopSource.addFeatures(
    stops.value.map(
      (stop) =>
        new Feature({
          geometry: new Point(fromLonLat([stop.lng, stop.lat])),
          number: stop.number,
        }),
    ),
  );
};

const drawEateries = () => {
  markerSource.clear();

  markerSource.addFeatures(
    eateries.value.map(
      (eatery) =>
        new Feature({
          geometry: new Point(
            fromLonLat([eatery.location.lng, eatery.location.lat]),
          ),
          typeId: eatery.typeId,
          venueTypeId: eatery.venueTypeId,
          eatery,
        }),
    ),
  );
};

watch(
  () => props.eateries,
  () => drawEateries(),
);

watch(
  () => props.stops,
  () => drawStops(),
);

watch(
  () => props.trip?.route.geometry,
  () => {
    selectedEatery.value = undefined;

    void nextTick(() => drawTrip());
  },
);

onMounted(() => {
  map.value = new Map({
    target: mapElement.value,
    layers: [
      new TileLayer({ source: new OSM() }),
      new VectorLayer({
        source: routeSource,
        style: new Style({ stroke: new Stroke({ color: '#29719f', width: 5 }) }),
      }),
      markerLayer,
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

  if (pulseElement.value) {
    pulseOverlay.setElement(pulseElement.value);
    map.value.addOverlay(pulseOverlay);
  }

  map.value.on('pointermove', (event) => {
    if (event.dragging) {
      return;
    }

    const eatery = map.value?.forEachFeatureAtPixel(
      event.pixel,
      (feature) => feature.get('eatery') as TripEatery | undefined,
      { layerFilter: (layer) => layer === markerLayer },
    );

    map.value
      ?.getTargetElement()
      .style.setProperty('cursor', eatery ? 'pointer' : '');

    if (eatery?.key === hoveredEatery.value?.key) {
      return;
    }

    hoveredFromMap.value = true;
    hoveredEatery.value = eatery;
  });

  map.value.on('singleclick', (event) => {
    const eatery = map.value?.forEachFeatureAtPixel(
      event.pixel,
      (feature) => feature.get('eatery') as TripEatery | undefined,
      { layerFilter: (layer) => layer === markerLayer },
    );

    if (eatery) {
      selectEatery(eatery);
    }
  });

  drawTrip();
});
</script>

<template>
  <div
    class="map-container relative -mb-3 flex max-h-full min-h-[500px] flex-1 flex-col overflow-hidden md:flex-row"
  >
    <Loader
      class="z-50"
      size="size-16"
      width="border-8"
      :display="form.processing"
      background
    />

    <TripPanel
      ref="tripPanel"
      :trip="trip"
      :eateries="eateries"
      :stops="stops"
      :selected="selectedEatery"
      @edit="openPlanner('edit')"
      @new="openPlanner('new')"
      @select="selectEatery"
      @back="selectedEatery = undefined"
      :hovered-key="hoveredEatery?.key"
      :scroll-to-hovered="hoveredFromMap"
      @hover="hoverFromList"
    />

    <div class="relative flex-1 md:ml-[28rem]">
      <div class="absolute right-0 bottom-32 z-10 size-28 md:bottom-0">
        <FilterMap
          v-if="trip"
          :set-filters="currentFilters"
          @filters-updated="handleFiltersChange"
        />
      </div>

      <div
        ref="mapElement"
        class="size-full"
      />

      <div
        ref="pulseElement"
        class="pointer-events-none relative size-12"
      >
        <span
          class="absolute inset-0 animate-ping rounded-full bg-primary-dark/40"
        />
      </div>
    </div>
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
