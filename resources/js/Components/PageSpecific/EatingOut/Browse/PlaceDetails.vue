<script setup lang="ts">
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import Card from '@/Components/Card.vue';
import PlaceDetailsContent from '@/Components/PageSpecific/EatingOut/Browse/PlaceDetailsContent.vue';

withDefaults(
  defineProps<{
    show: boolean;
    placeId: number;
    branchId?: number;
  }>(),
  {
    show: false,
    branchId: undefined,
  },
);

defineEmits(['close']);
</script>

<template>
  <Sidebar
    :open="show"
    side="left"
    size="2xl"
    @close="$emit('close')"
  >
    <Card class="flex h-screen flex-col space-y-3 p-0!">
      <PlaceDetailsContent
        v-if="show"
        :place-id="placeId"
        :branch-id="branchId"
      >
        <template #header>
          <div
            class="flex w-full items-center justify-between border-b border-grey-off bg-grey-light p-3"
          >
            <h2 class="text-xl font-semibold">Place Details</h2>
          </div>
        </template>
      </PlaceDetailsContent>

      <div class="content_hint" />
    </Card>
  </Sidebar>
</template>
