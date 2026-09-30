<script lang="ts" setup>
import CoeliacCompactFooter from '@/Layouts/Components/CoeliacCompactFooter.vue';
import { MetaProps } from '@/types/DefaultProps';
import CoeliacTravelCardsHeader from '@/Layouts/Components/CoeliacTravelCardsHeader.vue';

withDefaults(
  defineProps<{ meta: MetaProps; showBack?: boolean; hideFooter?: boolean }>(),
  { showBack: false, hideFooter: false },
);
</script>

<template>
  <div
    class="flex h-screen flex-col bg-gray-100"
    style="padding-bottom: var(--sticky-bottom, 0px)"
  >
    <CoeliacTravelCardsHeader
      :metas="meta"
      :show-back="showBack"
    />

    <section
      class="mx-auto flex w-full max-w-5xl flex-1 flex-col space-y-3 p-4"
    >
      <slot />
    </section>

    <CoeliacCompactFooter v-if="!hideFooter" />
  </div>
</template>
