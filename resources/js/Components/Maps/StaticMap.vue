<script lang="ts" setup>
import { ref } from 'vue';
import Modal from '@/Components/Overlays/Modal.vue';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';
import { StaticMapPropDefaults, StaticMapProps } from '@/Components/Maps/Props';

const props = withDefaults(
  defineProps<StaticMapProps>(),
  StaticMapPropDefaults,
);

const openModal = ref(false);

const styles = () => ({
  background: `url(/static/map/${props.lat},${props.lng}) no-repeat 50% 50%`,
  lineHeight: 0,
  cursor: 'pointer',
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
