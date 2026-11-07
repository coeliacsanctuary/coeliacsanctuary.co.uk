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
import { router, usePage, usePoll } from '@inertiajs/vue3';
import Loader from '@/Components/Loader.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';
import FilterMap from '@/Components/PageSpecific/EatingOut/Browse/FilterMap.vue';
import TripPanel from '@/Components/PageSpecific/EatingOut/TripPlanner/TripPanel.vue';
import ShareTripModal from '@/Components/PageSpecific/EatingOut/TripPlanner/ShareTripModal.vue';
import TripPlannerForm from '@/Components/PageSpecific/EatingOut/TripPlanner/TripPlannerForm.vue';
import { EateryFilterKeys, EateryFilters } from '@/types/EateryTypes';
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
import 'ol/ol.css';
import CoeliacCompact from '@/Layouts/CoeliacCompact.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import {
  Trip,
  TripEatery,
  TripLocation,
  TripRoute,
  TripStop,
  TripSummary,
} from '@/types/TripPlannerTypes';

defineOptions({
  layout: CoeliacCompact,
});

const props = defineProps<{
  trip?: Trip;
  canEdit?: boolean;
  eateries?: TripEatery[];
  stops?: TripStop[];
  summary?: TripSummary;
  destination?: TripLocation | null;
}>();

const plannerMode: Ref<'new' | 'edit' | undefined> = ref();
const processing = ref(false);

const isEditing = computed((): boolean => plannerMode.value === 'edit');

const openPlanner = (mode: 'new' | 'edit') => {
  plannerMode.value = mode;
};

const route = computed((): TripRoute | undefined => props.trip?.route);
const eateries = computed((): TripEatery[] => props.eateries ?? []);
const stops = computed((): TripStop[] => props.stops ?? []);

const { start: startSummaryPoll, stop: stopSummaryPoll } = usePoll(
  3000,
  { only: ['summary'] },
  { autoStart: false, background: 'pause' },
);

const pollForSummary = () => {
  if (props.summary?.status === 'pending') {
    startSummaryPoll();

    return;
  }

  stopSummaryPoll();
};

watch(
  () => props.summary?.status,
  () => pollForSummary(),
);

const selectedEatery: Ref<TripEatery | undefined> = ref();
const hoveredEatery: Ref<TripEatery | undefined> = ref();
const hoveredFromMap = ref(false);
const showShare = ref(false);

const shareTitle = computed((): string =>
  [props.trip?.points[0], props.trip?.points[props.trip.points.length - 1]]
    .map((point) => point?.label.split(',')[0] ?? '')
    .join(' to '),
);

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

  return (
    panelBounds.left < mapBounds.right && panelBounds.right > mapBounds.left
  );
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

const selectEatery = (eatery: TripEatery, source: 'list' | 'map' = 'list') => {
  selectedEatery.value = eatery;

  useJourneyTracking().logEvent('clicked', 'TripPlanner/Place', {
    eateryId: eatery.id,
    branchId: eatery.branchId,
    source,
    title: eatery.name,
  });

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
    const value = filters[key]
      .filter((item) => item.checked)
      .map((item) => item.value)
      .join(',');

    if (value) {
      query[key] = value;
    }
  });

  useJourneyTracking().logEvent('other', 'TripPlanner/Filter', query);

  router.get(page.url.split('?')[0], query, {
    only: ['eateries'],
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

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

  map.value
    .getView()
    .fit(routeSource.getExtent(), { padding: [30, 30, 30, 30] });
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
    processing.value = false;

    void nextTick(() => drawTrip());
  },
);

onMounted(() => {
  pollForSummary();

  map.value = new Map({
    target: mapElement.value,
    layers: [
      new TileLayer({ source: new OSM() }),
      new VectorLayer({
        source: routeSource,
        style: new Style({
          stroke: new Stroke({ color: '#29719f', width: 5 }),
        }),
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
      selectEatery(eatery, 'map');
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
      :display="processing"
      background
    />

    <TripPanel
      v-if="trip"
      ref="tripPanel"
      :trip="trip"
      :eateries="eateries"
      :stops="stops"
      :summary="summary"
      :selected="selectedEatery"
      :hovered-key="hoveredEatery?.key"
      :scroll-to-hovered="hoveredFromMap"
      @edit="openPlanner('edit')"
      @new="openPlanner('new')"
      @select="selectEatery($event, 'list')"
      @back="selectedEatery = undefined"
      @hover="hoverFromList"
      @share="showShare = true"
    />

    <div
      class="relative flex-1"
      :class="{ 'md:ml-[28rem]': trip }"
    >
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
        v-if="!trip"
        class="absolute inset-0 z-20 flex justify-center overflow-y-auto bg-white/30 p-4 backdrop-blur-[2px]"
      >
        <Card class="my-auto flex h-fit w-full max-w-5xl flex-col space-y-4">
          <div class="flex flex-col space-y-2">
            <Heading :border="false">Gluten Free Trip Planner</Heading>

            <p class="prose max-w-none md:prose-lg">
              Planning a road trip, a day out or a long drive? Tell me where
              you're starting from and where you're going, and I'll find the
              gluten free places to eat along your route.
            </p>
          </div>

          <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
            <TripPlannerForm
              mode="new"
              :destination="destination"
              @processing="processing = $event"
            />

            <div
              class="flex flex-col space-y-3 border-t border-grey-off pt-4 md:border-t-0 md:border-l md:pt-0 md:pl-8"
            >
              <SubHeading classes="mb-2!">
                How the trip planner works
              </SubHeading>

              <p class="prose max-w-none">
                Every place in my eating out guide that's within your chosen
                distance of the route is shown on the map and listed in journey
                order, so you can see what's coming up as you travel. Add up to
                five stops along the way, and I'll suggest places to stop and
                eat every hour, hour and a half, two hours or three hours of
                driving.
              </p>

              <p class="prose max-w-none">
                Your trip is saved with its own link, so you can share it with
                whoever you're travelling with, or email it to yourself and pick
                it up again on another device. Filter the results to just cafes,
                restaurants or places with the features you need, and tap any
                place to see its details and reviews.
              </p>
            </div>
          </div>
        </Card>
      </div>

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

  <ShareTripModal
    v-if="trip"
    :key="trip.key"
    :open="showShare"
    :trip-key="trip.key"
    :title="`Gluten free places from ${shareTitle}`"
    :can-edit="!!canEdit"
    @close="showShare = false"
  />

  <Modal
    :open="plannerMode !== undefined"
    closeable
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

    <div class="p-4">
      <TripPlannerForm
        v-if="plannerMode"
        :key="plannerMode"
        :mode="plannerMode"
        :trip="trip"
        :can-edit="canEdit"
        @processing="processing = $event"
        @success="
          plannerMode = undefined;
          processing = false;
        "
      />
    </div>
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
