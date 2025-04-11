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

const apiKey: string = import.meta.env.VITE_GOOGLE_MAPS_STATIC_KEY as string;

const backgroundUrl = new URL('https://maps.googleapis.com/maps/api/staticmap');
backgroundUrl.searchParams.set('center', `${props.lat},${props.lng}`);
backgroundUrl.searchParams.set('size', '600x600');
backgroundUrl.searchParams.set('maptype', 'roadmap');
backgroundUrl.searchParams.set(
  'markers',
  `color:red|label:|${props.lat},${props.lng}`,
);
backgroundUrl.searchParams.set('key', apiKey);

const styles = () => ({
  background: `url(${backgroundUrl.toString()}) no-repeat 50% 50%`,
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
