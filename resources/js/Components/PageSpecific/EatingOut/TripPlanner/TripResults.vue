<script setup lang="ts">
import { computed } from 'vue';
import {
  TripEatery,
  TripStop,
  TripSummary,
  TripTown,
} from '@/types/TripPlannerTypes';
import {
  formatDuration,
  stopName,
} from '@/support/eating-out/trip-planner/format';
import TripTownLink from '@/Components/PageSpecific/EatingOut/TripPlanner/TripTownLink.vue';
import TripEateryRow from '@/Components/PageSpecific/EatingOut/TripPlanner/TripEateryRow.vue';
import TripSummaryText from '@/Components/PageSpecific/EatingOut/TripPlanner/TripSummaryText.vue';

const props = defineProps<{
  eateries: TripEatery[];
  stops: TripStop[];
  summary?: TripSummary;
  towns?: (TripTown | null)[];
  hoveredKey?: string;
}>();

const viaTown = (stop: TripStop): TripTown | null => {
  if (stop.type !== 'via') {
    return null;
  }

  const viaIndex = props.stops
    .filter((item) => item.type === 'via')
    .indexOf(stop);

  return props.towns?.[viaIndex + 1] ?? null;
};

const destinationTown = computed(
  (): TripTown | null => props.towns?.[props.towns.length - 1] ?? null,
);

const summaryPending = computed(
  (): boolean => props.summary?.status === 'pending',
);

defineEmits(['select', 'hover']);

const nearbyEateries = (stop: TripStop): TripEatery[] =>
  props.eateries
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
  const boundaries = [0, ...props.stops.map((stop) => stop.duration), Infinity];

  return boundaries.slice(0, -1).map((from, index) => ({
    title:
      index === 0
        ? `Before ${stopName(props.stops[0], props.stops)}`
        : index === props.stops.length
          ? `After ${stopName(props.stops[index - 1], props.stops)}`
          : `${stopName(props.stops[index - 1], props.stops)} to ${stopName(props.stops[index], props.stops)}`,
    eateries: props.eateries.filter(
      (eatery) =>
        eatery.durationAlongRoute >= from &&
        eatery.durationAlongRoute < boundaries[index + 1],
    ),
  }));
});
</script>

<template>
  <div class="flex flex-col">
    <template v-if="!stops.length">
      <TripSummaryText
        :pending="summaryPending"
        :html="summary?.overview"
        label="About this trip"
      />

      <TripEateryRow
        v-for="eatery in eateries"
        :key="eatery.key"
        :eatery="eatery"
        :highlighted="eatery.key === hoveredKey"
        @select="$emit('select', $event)"
        @hover="$emit('hover', $event)"
      />

      <p
        v-if="!eateries.length"
        class="p-3 text-grey-dark"
      >
        There aren't any places along this route yet.
      </p>
    </template>

    <template v-else>
      <div
        v-for="stop in stops"
        :key="`stop-${stop.number}`"
        class="flex flex-col"
      >
        <p
          class="bg-secondary/20 p-2 font-semibold"
          v-text="
            `${stopName(stop, stops)}, about ${formatDuration(stop.duration)} in`
          "
        />

        <TripSummaryText
          :pending="summaryPending"
          :html="summary?.stops[stop.number]"
        />

        <TripEateryRow
          v-for="eatery in nearbyEateries(stop)"
          :key="`${stop.number}-${eatery.key}`"
          :eatery="eatery"
          :highlighted="eatery.key === hoveredKey"
          @select="$emit('select', $event)"
          @hover="$emit('hover', $event)"
        />

        <p
          v-if="!nearbyEateries(stop).length"
          class="p-2 text-sm text-grey-dark"
        >
          No places within half an hour of this stop.
        </p>

        <TripTownLink
          v-if="viaTown(stop)"
          :town="viaTown(stop) as TripTown"
        />
      </div>

      <p
        class="bg-grey-light p-2 font-semibold"
        v-text="`All places (${eateries.length})`"
      />

      <TripSummaryText
        :pending="summaryPending"
        :html="summary?.overview"
        label="About this trip"
      />

      <div
        v-for="leg in legs"
        :key="leg.title"
        class="flex flex-col"
      >
        <p
          class="border-b border-grey-off p-2 text-sm font-semibold text-grey-darker"
          v-text="`${leg.title} (${leg.eateries.length})`"
        />

        <TripEateryRow
          v-for="eatery in leg.eateries"
          :key="eatery.key"
          :eatery="eatery"
          :highlighted="eatery.key === hoveredKey"
          @select="$emit('select', $event)"
          @hover="$emit('hover', $event)"
        />
      </div>
    </template>

    <TripTownLink
      v-if="destinationTown"
      :town="destinationTown"
      class="border-t border-grey-off"
    />
  </div>
</template>
