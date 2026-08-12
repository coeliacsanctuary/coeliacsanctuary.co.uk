<script setup lang="ts">
import { EateryCountryPropItem } from '@/types/EateryTypes';
import { computed, ref, useTemplateRef } from 'vue';
import { router } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import useScreensize from '@/composables/useScreensize';
import EateryCountryCountyCard from '@/Components/PageSpecific/EatingOut/Index/EateryCountryCountyCard.vue';
import SnapScrollRow from '@/Components/SnapScrollRow.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
  country: EateryCountryPropItem;
}>();

const show = ref(false);
const showDescription = ref(false);
const countySearch = ref('');

const filteredCounties = computed(() =>
  props.country.list.filter((county) =>
    county.name.toLowerCase().includes(countySearch.value.toLowerCase()),
  ),
);

const hasSingleCounty = computed(() => props.country.counties === 1);

const showTopCounties = computed(
  () =>
    !hasSingleCounty.value &&
    props.country.top_counties &&
    props.country.top_counties.length > 1,
);

const toggleLabel = computed(() => {
  if (hasSingleCounty.value) {
    return `View gluten free places in ${props.country.list[0].name}`;
  }

  if (show.value) {
    return `Hide counties in ${props.country.name}`;
  }

  return `View all ${props.country.counties} counties in ${props.country.name}`;
});

const toggleButton = useTemplateRef<HTMLButtonElement>('toggleButton');

/** Once the county list has finished collapsing, bring the toggle back into view. */
const scrollToToggle = (): void => {
  toggleButton.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const toggle = (): void => {
  if (show.value) {
    show.value = false;
    return;
  }

  useJourneyTracking().logEvent('clicked', 'EateryCountryCard', {
    country: props.country.name,
  });

  if (hasSingleCounty.value) {
    router.get(`/wheretoeat/${props.country.list[0].slug}`);
    return;
  }

  show.value = true;
};
</script>

<template>
  <Card
    class="overflow-hidden"
    no-padding
  >
    <div class="relative h-32 overflow-hidden xs:h-40 md:h-48">
      <img
        :src="country.image"
        :alt="`Gluten free places to eat in ${country.name}`"
        class="absolute top-0 left-0 h-full w-full object-cover object-center"
        loading="lazy"
      />

      <div
        class="absolute top-0 left-0 h-full w-full bg-linear-to-t from-black/70 via-black/20 to-transparent"
      />

      <div
        class="absolute bottom-0 flex w-full flex-col gap-2 p-3 xs:flex-row xs:items-end xs:justify-between"
      >
        <SubHeading
          as="h2"
          classes="text-white drop-shadow-lg"
        >
          {{ country.name }}
        </SubHeading>

        <ul class="flex flex-wrap gap-2">
          <li
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base"
          >
            {{ country.counties }}
            {{ pluralise('county', country.counties) }}
          </li>

          <li
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base"
          >
            {{ country.eateries }} {{ pluralise('place', country.eateries) }}
          </li>
        </ul>
      </div>
    </div>

    <div class="flex flex-col space-y-6 p-4">
      <div>
        <div class="relative">
          <div
            class="prose max-w-none md:prose-lg"
            :class="{ 'max-h-24 overflow-hidden': !showDescription }"
            v-html="country.description"
          />

          <div
            v-if="!showDescription"
            class="absolute bottom-0 h-12 w-full bg-linear-to-t from-white to-transparent"
          />
        </div>

        <button
          type="button"
          class="mt-1 cursor-pointer text-sm font-semibold text-primary-dark transition hover:text-black md:text-base"
          @click="showDescription = !showDescription"
        >
          {{ showDescription ? 'Read less' : 'Read more' }}
        </button>
      </div>

      <div
        v-if="showTopCounties"
        class="flex flex-col space-y-3"
      >
        <SubHeading
          as="h3"
          text-size="xs"
        >
          Top rated areas in {{ country.name }}
        </SubHeading>

        <SnapScrollRow
          :items="country.top_counties ?? []"
          :label="(county) => `Show ${county.name}`"
        >
          <template #default="{ item, itemClasses }">
            <EateryCountryCountyCard
              :county="item"
              :country="country.name"
              :class="itemClasses"
              top
            />
          </template>
        </SnapScrollRow>
      </div>

      <transition
        enter-active-class="duration-300 ease-out"
        enter-from-class="-translate-y-3 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-3 opacity-0"
        @after-leave="scrollToToggle"
      >
        <div
          v-show="show"
          class="flex flex-col space-y-3 border-t border-grey-off-light pt-6"
        >
          <div
            class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:space-x-4"
          >
            <SubHeading
              as="h3"
              text-size="xs"
            >
              All {{ country.counties }}
              {{ pluralise('county', country.counties) }} in {{ country.name }}
            </SubHeading>

            <FormInput
              v-model="countySearch"
              :name="`county-search-${country.name}`"
              label=""
              :placeholder="`Search for a county in ${country.name}...`"
              hide-label
              borders
              class="w-full sm:max-w-xs"
              :size="
                useScreensize().screenIsGreaterThan('md') ? 'large' : 'default'
              "
            />
          </div>

          <div
            v-if="filteredCounties.length"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
          >
            <EateryCountryCountyCard
              v-for="county in filteredCounties"
              :key="county.slug"
              :county="county"
              :country="country.name"
            />
          </div>

          <p
            v-else
            class="py-8 text-center text-lg"
          >
            No counties found in {{ country.name }}, try updating your search!
          </p>
        </div>
      </transition>
    </div>

    <button
      ref="toggleButton"
      type="button"
      class="flex w-full cursor-pointer items-center justify-center space-x-2 bg-primary-lightest/60 p-4 text-base font-semibold transition hover:bg-primary-light/60 md:text-lg"
      @click="toggle()"
    >
      <span v-text="toggleLabel" />

      <ChevronDownIcon
        v-if="!hasSingleCounty"
        class="size-6 transition"
        :class="{ 'rotate-180 transform': show }"
      />
    </button>
  </Card>
</template>
