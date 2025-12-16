<script lang="ts" setup>
import AiOverviewCard from '@/Components/AiOverviewCard.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = defineProps<{
  productName: string;
  productId: number;
}>();

const emit = defineEmits(['onError']);

const getEndpoint = (): string => {
  return `/api/shop/products/${props.productId}/sealiac`;
};

// useJourneyTracking().logWhenVisible(
//   useTemplateRef('card'),
//   'scrolled_into_view',
//   'ShopProduct/AiOverview',
//   {
//     title: props.productName,
//   },
// );
</script>

<template>
  <AiOverviewCard
    v-bind="$attrs"
    ref="card"
    :endpoint="getEndpoint()"
    @on-error="$emit('onError')"
  >
    <template #title>
      Here's what Sealiac the Seal thinks about our {{ productName }}
    </template>

    <template #helpIntro>
      Sealiac the Seal is the Coeliac Sanctuary mascot, the text overview of our
      {{ productName }} product was generated using AI by analysing the reviews
      submitted by previous customers.
    </template>
  </AiOverviewCard>
</template>
