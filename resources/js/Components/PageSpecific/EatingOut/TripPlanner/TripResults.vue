<script setup lang="ts">
import { computed } from 'vue';
import { TripEatery, TripStop } from '@/types/TripPlannerTypes';
import { formatDuration } from '@/support/eating-out/trip-planner/format';
import TripEateryRow from '@/Components/PageSpecific/EatingOut/TripPlanner/TripEateryRow.vue';

const props = defineProps<{
  eateries: TripEatery[];
  stops: TripStop[];
  hoveredKey?: string;
}>();

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
        ? 'Before stop 1'
        : index === props.stops.length
          ? 'After the last stop'
          : `Stop ${index} to stop ${index + 1}`,
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
            `Stop ${stop.number}, about ${formatDuration(stop.duration)} in`
          "
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
      </div>

      <p
        class="bg-grey-light p-2 font-semibold"
        v-text="`All places (${eateries.length})`"
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
  </div>
</template>
