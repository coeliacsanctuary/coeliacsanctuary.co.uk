<script setup lang="ts">
import { computed, nextTick, Ref, ref, watch } from 'vue';
import { Trip, TripEatery, TripStop } from '@/types/TripPlannerTypes';
import {
  formatDuration,
  metersToMiles,
} from '@/support/eating-out/trip-planner/format';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import PlaceDetailsContent from '@/Components/PageSpecific/EatingOut/Browse/PlaceDetailsContent.vue';
import TripResults from '@/Components/PageSpecific/EatingOut/TripPlanner/TripResults.vue';
import {
  ArrowLeftIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  ShareIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
  trip?: Trip;
  eateries: TripEatery[];
  stops: TripStop[];
  selected?: TripEatery;
  hoveredKey?: string;
  scrollToHovered?: boolean;
}>();

const emits = defineEmits(['edit', 'new', 'select', 'back', 'hover', 'share']);

const expanded = ref(false);
const body: Ref<HTMLDivElement | undefined> = ref();
const listScrollTop = ref(0);

const title = computed((): string =>
  [props.trip?.points[0], props.trip?.points[props.trip.points.length - 1]]
    .map((point) => point?.label.split(',')[0] ?? '')
    .join(' → '),
);

const select = (eatery: TripEatery) => {
  listScrollTop.value = body.value?.scrollTop ?? 0;

  emits('select', eatery);
};

watch(
  () => props.hoveredKey,
  (key) => {
    if (!key || !props.scrollToHovered || props.selected) {
      return;
    }

    void nextTick(() => {
      const row = body.value?.querySelector(`[data-eatery-key="${key}"]`);

      if (!row || !body.value) {
        return;
      }

      const rowBounds = row.getBoundingClientRect();
      const bodyBounds = body.value.getBoundingClientRect();

      if (
        rowBounds.top >= bodyBounds.top &&
        rowBounds.bottom <= bodyBounds.bottom
      ) {
        return;
      }

      row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  },
);

watch(
  () => props.selected,
  (selected, previous) => {
    if (selected) {
      expanded.value = true;

      if (!previous) {
        listScrollTop.value = body.value?.scrollTop ?? listScrollTop.value;
      }

      void nextTick(() => body.value?.scrollTo({ top: 0 }));

      return;
    }

    void nextTick(() => body.value?.scrollTo({ top: listScrollTop.value }));
  },
);
</script>

<template>
  <div
    class="absolute inset-x-0 bottom-0 z-20 flex max-h-[70%] flex-col rounded-t-xl bg-white shadow-lg md:inset-x-auto md:inset-y-0 md:left-0 md:max-h-none md:w-[28rem] md:rounded-none md:shadow-md"
  >
    <button
      v-if="trip"
      type="button"
      class="flex w-full cursor-pointer items-center justify-center gap-1 pt-2 text-sm text-grey-dark md:hidden"
      @click="expanded = !expanded"
    >
      {{ expanded ? 'Hide places' : 'Show places' }}

      <component
        :is="expanded ? ChevronDownIcon : ChevronUpIcon"
        class="size-4"
      />
    </button>

    <div class="flex flex-col gap-2 border-b border-grey-off p-3">
      <template v-if="trip">
        <div class="flex items-start justify-between gap-2">
          <p
            class="font-semibold"
            v-text="title"
          />

          <button
            type="button"
            class="shrink-0 cursor-pointer rounded-md p-1 text-grey-dark transition hover:bg-grey-light hover:text-primary-dark"
            aria-label="Share your trip"
            @click="$emit('share')"
          >
            <ShareIcon class="size-5" />
          </button>
        </div>

        <p class="text-sm text-grey-dark">
          {{ metersToMiles(trip.route.distance) }} miles &middot;
          {{ formatDuration(trip.route.duration) }} &middot;
          <template v-if="stops.length">
            {{ stops.length }} suggested stop{{ stops.length > 1 ? 's' : '' }}
            &middot;
          </template>
          <template v-if="trip.points.length > 2">
            via {{ trip.points.length - 2 }} stop{{
              trip.points.length > 3 ? 's' : ''
            }}
            &middot;
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
          @click="$emit('edit')"
        />

        <CoeliacButton
          as="button"
          type="button"
          theme="primary"
          :label="trip ? 'New trip' : 'Plan a trip'"
          @click="$emit('new')"
        />
      </div>
    </div>

    <div
      v-if="trip"
      ref="body"
      class="min-h-0 flex-1 overflow-y-auto transition-[max-height] duration-300 ease-in-out md:max-h-none"
      :class="expanded ? 'max-h-[70vh]' : 'max-h-0'"
    >
      <template v-if="selected">
        <button
          type="button"
          class="flex cursor-pointer items-center gap-2 p-3 font-semibold text-primary-dark"
          @click="$emit('back')"
        >
          <ArrowLeftIcon class="size-4" />
          Back to your trip
        </button>

        <PlaceDetailsContent
          :place-id="selected.id"
          :branch-id="selected.branchId ?? undefined"
        />
      </template>

      <TripResults
        v-else
        :eateries="eateries"
        :stops="stops"
        :hovered-key="hoveredKey"
        @select="select"
        @hover="$emit('hover', $event)"
      />
    </div>
  </div>
</template>
