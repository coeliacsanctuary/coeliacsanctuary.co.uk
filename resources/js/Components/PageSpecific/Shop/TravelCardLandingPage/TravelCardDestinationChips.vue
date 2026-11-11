<script setup lang="ts">
import { TravelCardDestinationChip } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';

defineProps<{
  destinations: TravelCardDestinationChip[];
  current: string | null;
}>();
</script>

<template>
  <div
    v-if="destinations.length"
    class="flex w-full flex-col items-center space-y-2"
  >
    <p class="text-sm font-semibold text-grey-dark">Most searched for</p>

    <ul class="flex flex-wrap items-center justify-center gap-2">
      <li
        v-for="destination in destinations"
        :key="destination.term"
      >
        <Link
          :href="`/gluten-free-travel-translation-cards?term=${encodeURIComponent(destination.term)}`"
          class="inline-flex items-center space-x-2 rounded-full border px-3 py-1.5 text-sm font-semibold transition sm:text-base"
          :class="
            current?.toLowerCase() === destination.term.toLowerCase()
              ? 'border-primary-dark bg-primary-dark text-white'
              : 'border-primary-light bg-white text-grey-darker hover:border-primary hover:bg-primary-lightest'
          "
          preserve-scroll
          :only="['search', 'searchTerm', 'meta']"
        >
          <img
            v-if="destination.flag"
            :src="`https://flagcdn.com/24x18/${destination.flag}.png`"
            :alt="`${destination.term} flag`"
            width="24"
            height="18"
            loading="lazy"
            class="h-[18px] w-6 shrink-0 object-cover"
          />

          <span v-text="destination.term" />
        </Link>
      </li>
    </ul>
  </div>
</template>
