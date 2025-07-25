<script lang="ts" setup>
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import { computed } from 'vue';

const props = defineProps<{
  link: string;
  name: string;
  isNotNationwide: boolean;
  type: string;
  venueType?: string;
  cuisine?: string;
  website?: string;
  isBranch?: boolean;
  isFullyGf: boolean;
}>();

const icon = computed((): string => {
  if (props.type === 'Hotel / B&B') {
    return 'hotel';
  }

  if (props.type === 'Attraction') {
    return 'attraction';
  }

  return 'eatery';
});
</script>

<template>
  <div class="flex justify-between">
    <div class="mb-4 flex-1">
      <div class="flex items-center text-2xl font-semibold md:text-3xl">
        <div
          v-if="isNotNationwide"
          class="mr-2 w-10 pt-2 text-primary"
        >
          <Icon
            :name="icon"
            class="h-10 w-10"
          />
        </div>

        <h2 class="flex-1">
          <Link
            :href="link"
            class="hover:text-primary-dark hover:underline"
            prefetch
          >
            {{ name }}
          </Link>
        </h2>
      </div>

      <div class="my-2 w-fit">
        <span
          v-if="isFullyGf"
          class="rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-center text-sm font-semibold"
        >
          100% Gluten Free
        </span>
      </div>

      <h3
        v-if="isNotNationwide"
        class="mt-2 flex space-x-1 text-sm font-semibold text-grey-darker md:text-base"
      >
        <span v-if="isBranch">Nationwide Chain - </span>
        <span>{{ venueType }}</span>
        <span v-if="cuisine && cuisine !== 'English'">- {{ cuisine }} </span>
      </h3>

      <a
        v-if="website"
        :href="website"
        class="mt-2 flex items-center space-x-2 text-xs font-semibold text-grey transition-all ease-in-out hover:text-black md:text-sm"
        target="_blank"
      >
        <ArrowTopRightOnSquareIcon
          class="mr-2 inline-flex h-4 w-4 md:h-5 md:w-5"
        />

        Visit Website
      </a>
    </div>
  </div>
</template>
