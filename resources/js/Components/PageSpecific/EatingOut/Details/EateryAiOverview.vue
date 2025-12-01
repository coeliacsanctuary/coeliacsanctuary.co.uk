<script lang="ts" setup>
import AiOverviewCard from '@/Components/AiOverviewCard.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = defineProps<{
  eateryName: string;
  eateryId: number;
  branchId?: number;
}>();

const emit = defineEmits(['onError']);

const getEndpoint = (): string => {
  let url = `/api/wheretoeat/${props.eateryId}/sealiac`;

  if (props.branchId) {
    url += `?branchId=${props.branchId}`;
  }

  return url;
};

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryDetails/SealiacOverview',
  {
    eateryId: props.eateryId,
    branchId: props.branchId,
  },
);
</script>

<template>
  <div ref="card">
    <AiOverviewCard
      :endpoint="getEndpoint()"
      @on-error="$emit('onError')"
    >
      <template #title>
        Here's what Sealiac the Seal thinks about eating out at {{ eateryName }}
      </template>

      <template #helpIntro>
        Sealiac the Seal is the Coeliac Sanctuary mascot, the text overview of
        {{ eateryName }} was generated using AI by analysing the information we
        hold for {{ eateryName }}, and using reviews submitted through our
        website.
      </template>
    </AiOverviewCard>
  </div>
</template>
