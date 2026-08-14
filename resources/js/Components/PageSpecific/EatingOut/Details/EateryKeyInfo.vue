<script lang="ts" setup>
import { Days, DetailedEatery, OpeningTime } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { computed, useTemplateRef } from 'vue';
import {
  BookOpenIcon,
  ClockIcon,
  DevicePhoneMobileIcon,
  LinkIcon,
  MapPinIcon,
  WalletIcon,
} from '@heroicons/vue/24/solid';
import FacebookIcon from '@/Icons/FacebookIcon.vue';
import InstagramIcon from '@/Icons/InstagramIcon.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { ucfirst } from '@/helpers';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const days: Days[] = [
  'monday',
  'tuesday',
  'wednesday',
  'thursday',
  'friday',
  'saturday',
  'sunday',
];

const location = computed(() =>
  props.eatery.branch ? props.eatery.branch.location : props.eatery.location,
);

const eateryName = computed(() =>
  props.eatery.branch && props.eatery.branch.name
    ? props.eatery.branch.name
    : props.eatery.name,
);

const today = computed(
  () =>
    new Date()
      .toLocaleDateString('en-GB', { weekday: 'long' })
      .toLowerCase() as Days,
);

const openingTimesFor = (day: Days): OpeningTime | undefined =>
  props.eatery.opening_times?.days[day];

const isOpenOn = (day: Days): boolean => !!openingTimesFor(day)?.opens;

const openText = computed(() => {
  if (!props.eatery.opening_times?.is_open_now) {
    return 'Currently closed';
  }

  return `Open now, closes at ${props.eatery.opening_times.today.closes}`;
});

const averageExpense = computed(() => {
  if (!props.eatery.reviews.expense) {
    return null;
  }

  return '£'.repeat(parseInt(props.eatery.reviews.expense.value, 10));
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryDetails/Location',
  {
    eateryId: props.eatery.id,
    branchId: props.eatery.branch?.id,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4"
  >
    <SubHeading
      text-size="small"
      class="pb-2"
    >
      Where to find {{ eateryName }}
    </SubHeading>

    <div class="-mx-4 [&>div]:mb-0">
      <StaticMap
        map-classes="min-h-map-small"
        :title="`${eateryName} - ${location.address}`"
        :lat="location.lat"
        :lng="location.lng"
      />
    </div>

    <ul class="flex flex-col divide-y divide-grey-off/60">
      <li class="flex items-start gap-3 py-3">
        <MapPinIcon class="size-5 shrink-0 text-primary-dark" />
        <span
          class="text-sm font-semibold text-grey-darkest"
          v-html="location.address"
        />
      </li>

      <li v-if="eatery.website">
        <a
          class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest transition hover:text-primary-dark"
          :href="eatery.website"
          target="_blank"
          rel="nofollow"
        >
          <LinkIcon class="size-5 shrink-0 text-primary-dark" />
          <span>Visit website</span>
        </a>
      </li>

      <li v-if="eatery.menu">
        <a
          class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest transition hover:text-primary-dark"
          :href="eatery.menu"
          target="_blank"
          rel="nofollow"
        >
          <BookOpenIcon class="size-5 shrink-0 text-primary-dark" />
          <span>View gluten free menu</span>
        </a>
      </li>

      <li v-if="eatery.phone">
        <a
          class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest transition hover:text-primary-dark"
          :href="`tel:${eatery.phone}`"
        >
          <DevicePhoneMobileIcon class="size-5 shrink-0 text-primary-dark" />
          <span v-text="eatery.phone" />
        </a>
      </li>

      <li v-if="eatery.facebook_url">
        <a
          class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest transition hover:text-primary-dark"
          :href="eatery.facebook_url"
          target="_blank"
        >
          <FacebookIcon class="!size-5 shrink-0 text-primary-dark" />
          <span>Facebook</span>
        </a>
      </li>

      <li v-if="eatery.instagram_url">
        <a
          class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest transition hover:text-primary-dark"
          :href="eatery.instagram_url"
          target="_blank"
        >
          <InstagramIcon class="!size-5 shrink-0 text-primary-dark" />
          <span>Instagram</span>
        </a>
      </li>

      <li
        v-if="eatery.reviews.expense"
        class="flex items-center gap-3 py-3 text-sm font-semibold text-grey-darkest"
      >
        <WalletIcon class="size-5 shrink-0 text-primary-dark" />
        <span
          >{{ averageExpense }} &ndash; {{ eatery.reviews.expense.label }}</span
        >
      </li>
    </ul>

    <div
      v-if="eatery.opening_times"
      class="flex flex-col gap-2"
    >
      <div class="flex items-center gap-3">
        <ClockIcon class="size-5 shrink-0 text-primary-dark" />
        <span
          class="text-sm font-semibold"
          :class="
            eatery.opening_times.is_open_now
              ? 'text-green-dark'
              : 'text-red-dark'
          "
          v-text="openText"
        />
      </div>

      <ul class="flex flex-col divide-y divide-grey-off/60 text-sm">
        <li
          v-for="day in days"
          :key="day"
          class="flex justify-between py-1.5"
          :class="
            day === today ? 'font-semibold text-grey-darkest' : 'text-grey-dark'
          "
        >
          <span v-text="ucfirst(day)" />

          <span
            v-if="isOpenOn(day)"
            v-text="
              `${openingTimesFor(day)?.opens} - ${openingTimesFor(day)?.closes}`
            "
          />
          <span v-else>Closed</span>
        </li>
      </ul>
    </div>
  </Card>
</template>
