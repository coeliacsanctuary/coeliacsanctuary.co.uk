<script lang="ts" setup>
import { DetailedEatery } from '@/types/EateryTypes';
import { computed, ref } from 'vue';
import {
  BookOpenIcon,
  ClockIcon,
  DevicePhoneMobileIcon,
  LinkIcon,
  MapIcon,
  WalletIcon,
} from '@heroicons/vue/24/solid';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import EateryOpeningTimesModal from '@/Components/PageSpecific/EatingOut/Details/Modals/EateryOpeningTimesModal.vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const viewMap = ref(false);
const viewOpeningTimes = ref(false);

const averageExpense = computed(() => {
  if (!props.eatery.reviews.expense) {
    return null;
  }

  let rtr = '';

  for (
    let x = 0;
    x < parseInt(props.eatery.reviews.expense.value, 10);
    x += 1
  ) {
    rtr += '£';
  }

  return rtr;
});

const openText = computed(() => {
  if (!props.eatery.opening_times?.is_open_now) {
    return 'Currently Closed';
  }

  return `Open, closes at ${props.eatery.opening_times.today.closes}`;
});
</script>

<template>
  <ul class="flex flex-wrap items-center gap-2">
    <li
      v-if="eatery.county.id > 1 || eatery.branch"
      class="rounded-sm bg-primary-light/25 px-3 py-1 leading-none"
    >
      <a
        class="flex cursor-pointer items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
        @click.prevent="viewMap = true"
      >
        <MapIcon class="h-4 w-4" />
        <span>Map</span>
      </a>
    </li>

    <li
      v-if="eatery.website"
      class="rounded-sm bg-primary-light/25 px-3 py-1 leading-none"
    >
      <a
        class="flex items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
        :href="eatery.website"
        target="_blank"
      >
        <LinkIcon class="h-4 w-4" />
        <span>Website</span>
      </a>
    </li>

    <li
      v-if="eatery.phone"
      class="rounded-sm bg-primary-light/25 px-3 py-1 leading-none"
    >
      <a
        class="flex items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
        :href="'tel:' + eatery.phone"
        target="_blank"
      >
        <DevicePhoneMobileIcon class="h-4 w-4" />

        <span>Phone</span>
      </a>
    </li>

    <li
      v-if="eatery.menu"
      class="hidden rounded-sm bg-primary-light/25 px-3 py-1 leading-none xs:block"
    >
      <a
        class="flex items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
        :href="eatery.menu"
        target="_blank"
      >
        <BookOpenIcon class="h-4 w-4" />
        <span>GF Menu</span>
        <LinkIcon class="h-4 w-4" />
      </a>
    </li>

    <li
      v-if="eatery.reviews.expense"
      class="hidden rounded-sm bg-primary-light/25 px-3 py-1 leading-none md:block"
    >
      <a
        class="flex items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
      >
        <WalletIcon class="h-4 w-4" />
        <span>{{ averageExpense }} - {{ eatery.reviews.expense.label }}</span>
      </a>
    </li>

    <li
      v-if="eatery.opening_times"
      class="hidden rounded-sm bg-primary-light/25 px-3 py-1 leading-none xmd:block"
    >
      <a
        class="flex cursor-pointer items-center space-x-3 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black"
        @click.prevent="viewOpeningTimes = true"
      >
        <ClockIcon class="h-4 w-4" />
        <span>{{ openText }}</span>
      </a>
    </li>

    <Modal
      :open="viewMap"
      no-padding
      size="large"
      width="w-full"
      @close="viewMap = false"
    >
      <DynamicMap
        :title="`${eatery.branch && eatery.branch.name ? eatery.branch.name + ' - ' : ''} ${eatery.name} - ${eatery.branch ? eatery.branch.location.address : eatery.location.address}`"
        :lat="eatery.branch ? eatery.branch.location.lat : eatery.location.lat"
        :lng="eatery.branch ? eatery.branch.location.lng : eatery.location.lng"
      />
    </Modal>

    <EateryOpeningTimesModal
      :eatery-name="eatery.name"
      :show="viewOpeningTimes"
      :opening-times="eatery.opening_times"
      @close="viewOpeningTimes = false"
    />
  </ul>
</template>
