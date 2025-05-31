<script lang="ts" setup>
import { computed, ref } from 'vue';
import Modal from '@/Components/Overlays/Modal.vue';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';
import { StaticMapPropDefaults, StaticMapProps } from '@/Components/Maps/Props';

const props = withDefaults(
  defineProps<StaticMapProps>(),
  StaticMapPropDefaults,
);

const openModal = ref(false);

const url = computed(() => {
  let url = `/static/map/${props.lat},${props.lng}`;

  if (props.additionalParams) {
    const params = new URLSearchParams({
      params: JSON.stringify(props.additionalParams),
    });

    url += `?${params.toString()}`;
  }

  return url;
});

const styles = () => ({
  background: `url(${url.value}) no-repeat 50% 50%`,
  lineHeight: 0,
  cursor: props.canExpand ? 'pointer' : 'default',
});
</script>

<template>
  <div class="mb-1 h-full">
    <div
      :class="mapClasses"
      :style="styles()"
      class="w-full"
      @click.stop="props.canExpand ? (openModal = true) : undefined"
    />
  </div>

  <Modal
    :open="openModal"
    no-padding
    size="large"
    width="w-full"
    @close="openModal = false"
  >
    <div class="min-w-full">
      <DynamicMap
        :title="title"
        :lat="lat"
        :lng="lng"
      />
    </div>
  </Modal>
</template>
