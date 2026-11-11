<script setup lang="ts">
import { ShopDigitalCard } from '@/types/Shop';
import AddDigitalCardToBasket from '@/Components/PageSpecific/Shop/DigitalCards/AddDigitalCardToBasket.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = defineProps<{ card: ShopDigitalCard }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('row'),
  'scrolled_into_view',
  'TravelCardSearch/DigitalCardRow',
  {
    title: props.card.title,
  },
);
</script>

<template>
  <div
    ref="row"
    class="flex flex-wrap items-center justify-between gap-4 rounded-sm bg-white p-4 shadow-sm"
  >
    <div class="flex min-w-0 flex-col space-y-1">
      <p
        class="text-lg font-semibold text-primary-dark"
        v-text="card.title"
      />

      <p
        v-if="card.product"
        class="text-sm text-grey-darker"
      >
        Only {{ card.price.extra }} when bought with my
        <span
          class="font-semibold"
          v-text="card.product.title"
        />
      </p>
    </div>

    <div class="flex items-center gap-4">
      <p
        class="text-2xl font-semibold"
        v-text="card.price.default"
      />

      <AddDigitalCardToBasket :product="card" />
    </div>
  </div>
</template>
