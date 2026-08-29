<script lang="ts" setup>
import AiOverviewCard from '@/Components/AiOverviewCard.vue';

const props = defineProps<{
  productName: string;
  productId: number;
}>();

defineEmits(['onError']);

const getEndpoint = (): string => {
  return `/api/shop/products/${props.productId}/sealiac`;
};
</script>

<template>
  <AiOverviewCard
    v-bind="$attrs"
    :endpoint="getEndpoint()"
    @on-error="$emit('onError')"
  >
    <template #title>
      Here's what Sealiac the Seal thinks about my {{ productName }}
    </template>

    <template #helpIntro>
      Sealiac the Seal is the Coeliac Sanctuary mascot. The overview of my
      {{ productName }} was generated using AI by analysing the reviews
      submitted by previous customers.
    </template>
  </AiOverviewCard>
</template>
