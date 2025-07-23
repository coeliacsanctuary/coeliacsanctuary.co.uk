<script lang="ts" setup>
import AiOverviewCard from '@/Components/AiOverviewCard.vue';

const props = defineProps<{
  productName: string;
  productId: number;
}>();

const emit = defineEmits(['onError']);

const getEndpoint = (): string => {
  return `/api/shop/products/${props.productId}/sealiac`;
};
</script>

<template>
  <AiOverviewCard
    :endpoint="getEndpoint()"
    v-bind="$attrs"
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
