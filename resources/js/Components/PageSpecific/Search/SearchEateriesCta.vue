<script setup lang="ts">
import Info from '@/Components/Info.vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { LatLng } from '@/types/EateryTypes';
import { computed } from 'vue';
import * as polyline from '@googlemaps/polyline-codec';
import CoeliacButton from '@/Components/CoeliacButton.vue';

const props = defineProps<{ latlng?: LatLng; location: string }>();

defineEmits(['eatery-search']);

const googleCircle = computed(() => {
  if (!props.latlng) {
    return null;
  }

  const radiusKm = 5 * 1.60934;
  const points = [];

  for (let angle = 0; angle <= 360; angle += 10) {
    const angleRad = (angle * Math.PI) / 180;

    const x = (radiusKm / 111) * Math.cos(angleRad);

    const y =
      (radiusKm / (111 * Math.cos((props.latlng.lat * Math.PI) / 180))) *
      Math.sin(angleRad);

    points.push([props.latlng.lat + x, props.latlng.lng + y]);
  }

  return polyline.encode(points);
});
</script>

<template>
  <Info
    class="mx-3 flex flex-col justify-between space-y-2 xs:flex-row xs:space-x-2"
    no-icon
  >
    <div class="flex flex-col space-y-2">
      <p class="prose max-w-none md:prose-lg 2xl:prose-xl">
        If you're looking for places to eat in
        <strong v-text="location" />, you can find more detailed results in our
        <a
          class="inline-block cursor-pointer font-semibold"
          @click.prevent="$emit('eatery-search')"
        >
          Eating Out guide </a
        >, we've done a basic search within 5 miles of {{ location }}, but you
        can search a wider radius in our Eating Out Guide.
      </p>

      <div>
        <CoeliacButton
          :label="`See more results for ${location}`"
          size="lg"
          theme="secondary"
          as="button"
          bold
          @click="$emit('eatery-search')"
        />
      </div>
    </div>

    <div
      v-if="latlng"
      class="aspect-square w-full sm:max-w-[170px]"
    >
      <StaticMap
        :lat="latlng.lat"
        :lng="latlng.lng"
        :can-expand="false"
        map-classes="!bg-cover h-full"
        :additional-params="{
          path: `color:0xDBBC2599|fillcolor:0xDBBC254C|enc:${googleCircle}`,
          zoom: '11',
          size: '400x400',
        }"
      />
    </div>
  </Info>
</template>
