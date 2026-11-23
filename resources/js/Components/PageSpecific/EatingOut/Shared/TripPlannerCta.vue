<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{
  place: string;
  href: string;
}>();

const logClick = (): void => {
  useJourneyTracking().logEvent('clicked', 'TripPlannerCta', {
    title: props.place,
  });
};
</script>

<template>
  <Card class="relative overflow-hidden">
    <div
      class="absolute top-0 left-0 h-full w-full bg-secondary/20 xmd:bg-gradient-to-bl xmd:from-white xmd:to-secondary/5"
    >
      <img
        src="/images/misc/eating-out-trip-planner.png"
        alt=""
        class="h-full w-full object-cover object-center opacity-20"
        loading="lazy"
      />
    </div>

    <div class="relative z-10 flex flex-col space-y-3">
      <SubHeading>Heading to {{ place }}?</SubHeading>

      <p class="prose max-w-none">
        Plan your journey and I'll show you gluten free places to eat along the
        way, with suggested stops.
      </p>

      <CoeliacButton
        :href="href"
        theme="secondary"
        size="xl"
        :label="`Plan a trip to ${place}`"
        bold
        icon-position="right"
        :icon="ArrowRightIcon"
        classes="justify-center"
        @click="logClick"
      />
    </div>
  </Card>
</template>
