<script lang="ts" setup>
import CoeliacCompactFooter from '@/Layouts/Components/CoeliacCompactFooter.vue';
import { MetaProps } from '@/types/DefaultProps';
import CoeliacTravelCardsHeader from '@/Layouts/Components/CoeliacTravelCardsHeader.vue';

defineProps<{ meta: MetaProps }>();
</script>

<template>
  <div
    class="flex h-screen flex-col bg-gray-100"
    style="padding-bottom: var(--sticky-bottom, 0px)"
  >
    <CoeliacTravelCardsHeader :metas="meta" />

    <section class="mx-auto flex w-full flex-1 flex-col space-y-3 p-4">
      <slot />
    </section>

    <CoeliacCompactFooter />
  </div>
</template>
