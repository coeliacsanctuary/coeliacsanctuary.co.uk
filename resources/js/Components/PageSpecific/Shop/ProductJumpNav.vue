<script setup lang="ts">
import useJourneyTracking from '@/composables/useJourneyTracking';

defineProps<{ sections: { id: string; label: string }[] }>();
</script>

<template>
  <nav
    v-if="sections.length > 1"
    aria-label="Jump to a section of this page"
    class="-mx-3 overflow-x-auto px-3 sm:mx-0 sm:px-0"
  >
    <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
      <li
        v-for="section in sections"
        :key="section.id"
      >
        <a
          :href="`#${section.id}`"
          class="block rounded-full border border-primary-light bg-white px-4 py-2 text-sm font-semibold whitespace-nowrap shadow-sm transition hover:bg-primary-lightest hover:text-primary-dark md:text-base"
          @click="
            useJourneyTracking().logEvent('clicked', 'ShopProduct/JumpNav', {
              section: section.id,
            })
          "
          v-text="section.label"
        />
      </li>
    </ul>
  </nav>
</template>
