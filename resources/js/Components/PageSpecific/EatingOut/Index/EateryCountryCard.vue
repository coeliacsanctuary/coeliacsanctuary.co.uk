<script setup lang="ts">
import { EateryCountryPropItem } from '@/types/EateryTypes';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Heading from '@/Components/Heading.vue';
import EateryCountryCountyCard from '@/Components/PageSpecific/EatingOut/Index/EateryCountryCountyCard.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
  country: EateryCountryPropItem;
}>();

const show = ref(false);

const toggle = (): void => {
  if (show.value) {
    show.value = false;
    return;
  }

  useJourneyTracking().logEvent('clicked', 'EateryCountryCard', {
    country: props.country.name,
  });

  if (props.country.counties === 1) {
    router.get(`/wheretoeat/${props.country.list[0].slug}`);
    return;
  }

  show.value = true;
};
</script>

<template>
  <Card class="">
    <div
      class="z-10 flex cursor-pointer flex-col space-y-4"
      @click="toggle()"
    >
      <div class="flex items-center justify-between">
        <Heading
          :border="false"
          as="h2"
        >
          {{ country.name }}
        </Heading>

        <ChevronDownIcon
          class="size-8 transition md:size-10"
          :class="{ 'rotate-180 transform': show }"
        />
      </div>

      <p
        class="prose-md prose max-w-none md:prose-lg"
        v-html="country.description"
      />
    </div>

    <div
      v-if="
        !show && country.top_counties?.length && country.top_counties.length > 1
      "
      class="mt-6 flex flex-col space-y-3"
    >
      <div>
        <SubHeading as="h3">Top rated areas in {{ country.name }}</SubHeading>
      </div>

      <div
        class="grid gap-4 xmd:grid-cols-2 xmd:grid-rows-1 xmd:max-xl:[grid-auto-rows:0] xmd:max-xl:overflow-hidden xl:grid-cols-3"
      >
        <EateryCountryCountyCard
          v-for="county in country.top_counties"
          :key="county.slug"
          :county="county"
          :country="country.name"
          top
        />
      </div>
    </div>

    <transition
      enter-active-class="duration-300 ease-out"
      enter-from-class="-translate-y-3 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="-translate-y-3 opacity-0"
    >
      <div v-if="show">
        <div class="mt-3 grid gap-4 sm:grid-cols-2 xmd:grid-cols-3">
          <EateryCountryCountyCard
            v-for="county in country.list"
            :key="county.slug"
            :county="county"
            :country="country.name"
          />
        </div>
      </div>
    </transition>
  </Card>
</template>
