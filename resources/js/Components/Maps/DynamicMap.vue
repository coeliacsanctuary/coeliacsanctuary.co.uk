<script lang="ts" setup>
import { onMounted, Ref, ref } from 'vue';
import { loadScript } from '@/helpers';
import { MapModalPropDefaults, MapModalProps } from '@/Components/Maps/Props';
import useGoogleEvents from '@/composables/useGoogleEvents';

const props = withDefaults(defineProps<MapModalProps>(), MapModalPropDefaults);

const mapElement: Ref<HTMLDivElement> =
  ref<HTMLDivElement>() as Ref<HTMLDivElement>;

const apiKey: string = import.meta.env.VITE_GOOGLE_MAPS_DYNAMIC_KEY as string;

onMounted(() => {
  useGoogleEvents().googleEvent('event', 'dynamic-map', {
    event_category: 'dynamic-map',
    event_label: `dynamic-map-viewed-${props.lat},${props.lng}`,
  });

  loadScript(
    `https://maps.google.com/maps/api/js?key=${apiKey}&libraries=marker&v=weekly`,
  ).then(() => {
    const center: google.maps.LatLng = new google.maps.LatLng(
      props.lat,
      props.lng,
    );

    const map = new google.maps.Map(mapElement.value, {
      center,
      zoom: props.zoom,
      mapTypeControl: false,
      streetViewControl: false,
      rotateControl: false,
      fullscreenControl: false,
      gestureHandling: 'greedy',
      mapId: 'map',
    });

    new google.maps.marker.AdvancedMarkerElement({
      position: center,
      map,
    });
  });
});
</script>

<template>
  <div
    v-if="title"
    class="relative border-b border-grey-off bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
    v-html="title"
  />
  <div
    ref="mapElement"
    class="h-[80vh]"
  />
</template>
