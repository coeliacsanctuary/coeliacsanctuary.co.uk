<script setup lang="ts">
import { EateryCountryList, EateryCountryPropItem } from '@/types/EateryTypes';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{
  country: string;
  details: EateryCountryPropItem;
}>();

const show = ref(false);

const toggle = (): void => {
  if (show.value) {
    show.value = false;
    return;
  }

  useJourneyTracking().logEvent('clicked', 'EateryCountryCard', {
    country: props.country,
  });

  if (props.details.counties === 1) {
    router.get(`/wheretoeat/${props.details.list[0].slug}`);
    return;
  }

  show.value = true;
};

const formatNumber = (number: number): string => {
  switch (number) {
    case 1:
      return 'one';
    case 2:
      return 'two';
    case 3:
      return 'three';
    case 4:
      return 'four';
    case 5:
      return 'five';
    case 6:
      return 'six';
    case 7:
      return 'seven';
    case 8:
      return 'eight';
    case 9:
      return 'nine';
    case 10:
      return 'ten';
    default:
      return Intl.NumberFormat().format(number);
  }
};

const countyDescription = (county: EateryCountryList): string => {
  const bits = [];

  if (county.eateries && county.eateries !== 0) {
    bits.push(
      `<strong>${formatNumber(county.eateries)}</strong> gluten free place${
        county.eateries !== 1 ? 's' : ''
      } to eat`,
    );
  }

  if (county.branches && county.branches !== 0) {
    bits.push(
      `<strong>${formatNumber(
        county.branches,
      )}</strong> gluten free nationwide branch${
        county.branches !== 1 ? 'es' : ''
      }`,
    );
  }

  if (county.attractions && county.attractions !== 0) {
    bits.push(
      `<strong>${formatNumber(county.attractions)}</strong> attraction${
        county.attractions !== 1 ? 's' : ''
      } with gluten free options`,
    );
  }

  if (county.hotels && county.hotels !== 0) {
    bits.push(
      `<strong>${formatNumber(county.hotels)}</strong> hotel${
        county.hotels !== 1 ? 's' : ''
      } / B&B${county.hotels !== 1 ? 's' : ''} with gluten free options`,
    );
  }

  if (bits.length === 1) {
    return `There is ${bits[0]} listed in ${county.name} in our eating out guide.`;
  }

  if (bits.length === 2) {
    return `There is ${bits[0]} and ${bits[1]} listed in ${county.name} in our eating out guide.`;
  }

  if (bits.length === 3) {
    return `There is ${bits[0]}, ${bits[1]} and ${bits[2]} listed in ${county.name} in our eating out guide.`;
  }

  return `There is ${bits[0]}, ${bits[1]}, ${bits[2]} and ${bits[3]} listed in ${county.name} in our eating out guide.`;
};
</script>

<template>
  <div
    class="block space-y-3 rounded-sm bg-linear-to-br from-primary/50 to-primary-light/50 p-3 shadow-sm"
  >
    <div
      class="z-10 cursor-pointer"
      @click="toggle()"
    >
      <h3 class="mb-2 text-lg font-semibold md:text-2xl">
        {{ country }}
      </h3>

      <p class="prose-md prose max-w-none md:prose-lg">
        We've got <strong>{{ formatNumber(details.eateries) }}</strong> gluten
        places to eat across
        <strong>{{ formatNumber(details.counties) }}</strong>
        {{ details.counties !== 1 ? 'counties' : 'county' }}
        within {{ country }} listed in our eating out guide.
      </p>
    </div>

    <div class="mt-0!">
      <transition
        enter-active-class="duration-500 ease-out"
        enter-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="duration-500 ease-in"
        leave-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
      >
        <div
          v-if="show"
          class="transform rounded-sm"
        >
          <ul class="mt-3 flex flex-col space-y-3">
            <li
              v-for="county in details.list"
              :key="county.slug"
              class="group transform rounded-md border-b bg-white/80 p-3 shadow-inner transition duration-500 hover:scale-105 hover:bg-white/100 hover:shadow-lg"
            >
              <Link
                :href="'/wheretoeat/' + county.slug"
                prefetch="click"
                :on-before="
                  () =>
                    useJourneyTracking().logEvent(
                      'clicked',
                      'EateryCountryCard/CountyLink',
                      { country: country, county: county.name },
                    )
                "
              >
                <h4
                  class="text-md mb-2 transform font-semibold text-primary-dark transition duration-500 group-hover:text-lg md:text-xl md:group-hover:text-xl"
                >
                  {{ county.name }}
                </h4>
                <p
                  class="prose-md prose max-w-none md:prose-lg"
                  v-html="countyDescription(county)"
                />
              </Link>
            </li>
          </ul>
        </div>
      </transition>
    </div>
  </div>
</template>
