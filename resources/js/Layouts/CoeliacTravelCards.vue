<script lang="ts" setup>
import CoeliacCompactHeader from '@/Layouts/Components/CoeliacCompactHeader.vue';
import CoeliacCompactFooter from '@/Layouts/Components/CoeliacCompactFooter.vue';
import { MetaProps } from '@/types/DefaultProps';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import AdBlocker from '@/Components/AdBlocker.vue';
import CoeliacTravelCardsHeader from '@/Layouts/Components/CoeliacTravelCardsHeader.vue';

defineProps<{ meta: MetaProps }>();
</script>

<template>
  <div
    class="flex h-screen flex-col bg-gray-100"
    style="padding-bottom: var(--sticky-bottom, 0px)"
  >
    <CoeliacTravelCardsHeader :metas="meta" />

    <section class="mx-auto flex w-full flex-1 flex-col space-y-3">
      <slot />
    </section>

    <CoeliacCompactFooter ref="footerEl" />
  </div>

  <AdBlocker />
</template>
