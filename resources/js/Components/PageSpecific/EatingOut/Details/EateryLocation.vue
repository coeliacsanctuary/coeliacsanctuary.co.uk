<script lang="ts" setup>
import { DetailedEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { computed } from 'vue';
import {
  DevicePhoneMobileIcon,
  LinkIcon,
  MapIcon,
} from '@heroicons/vue/24/solid';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';
import SubHeading from '@/Components/SubHeading.vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const lat = computed(() =>
  props.eatery.branch
    ? props.eatery.branch.location.lat
    : props.eatery.location.lat,
);

const lng = computed(() =>
  props.eatery.branch
    ? props.eatery.branch.location.lng
    : props.eatery.location.lng,
);

const address = computed(() =>
  props.eatery.branch
    ? props.eatery.branch.location.address
    : props.eatery.location.address,
);

const eateryName = computed(() => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return props.eatery.branch.name;
  }

  return props.eatery.name;
});
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:p-6">
    <SubHeading class="!mb-2 lg:!mb-4">
      Where to find {{ eateryName }}
    </SubHeading>
    <div
      class="flex flex-col space-y-2 sm:flex-row sm:max-lg:space-x-3 sm:space-y-0 lg:space-x-4"
    >
      <div class="h-map-small w-full max-w-[600px] sm:w-1/2 lg:w-2/3">
        <StaticMap
          :title="`${eateryName} - ${eatery.branch ? eatery.branch.location.address : eatery.location.address}`"
          :lng="lng"
          :lat="lat"
        />
      </div>

      <ul class="sm:text-md flex flex-col space-y-3 sm:w-1/2 lg:space-y-4">
        <li class="flex items-center space-x-3">
          <MapIcon class="h-4 w-4 sm:h-6 sm:w-6 lg:w-8 lg:h-8" />
          <span
            class="text-sm font-semibold sm:text-base lg:text-lg"
            v-html="address"
          />
        </li>

        <li v-if="eatery.website">
          <a
            :href="eatery.website"
            target="_blank"
            class="flex items-center space-x-3 transition hover:text-primary-dark"
            rel="nofollow"
          >
            <LinkIcon class="h-4 w-4 sm:h-6 sm:w-6 lg:w-8 lg:h-8" />
            <span class="text-sm font-semibold sm:text-base lg:text-lg">
              Visit Website
            </span>
          </a>
        </li>

        <li v-if="eatery.phone">
          <a
            :href="'tel:' + eatery.phone"
            class="flex items-center space-x-3 transition hover:text-primary-dark"
          >
            <DevicePhoneMobileIcon
              class="h-4 w-4 sm:h-6 sm:w-6 lg:w-8 lg:h-8"
            />
            <span
              class="text-sm font-semibold sm:text-base lg:text-lg"
              v-text="eatery.phone"
            />
          </a>
        </li>
      </ul>
    </div>
  </Card>
</template>
