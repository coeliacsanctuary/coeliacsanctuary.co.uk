<script lang="ts" setup>
import { DocumentArrowUpIcon, MapIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { LatLng } from '@/types/EateryTypes';
import { computed } from 'vue';

const props = defineProps<{ latlng?: LatLng }>();

const linkCards = computed(() =>
  [
    {
      title: 'Map',
      description:
        'Browse an interactive map with all of the places we know about marked that offer gluten free!',
      icon: MapIcon,
      href: props.latlng
        ? `/wheretoeat/browse/${props.latlng.lat},${props.latlng.lng}/13`
        : undefined,
    },
    {
      title: 'Recommend a place',
      description:
        "Do you know somewhere that offers gluten free that we don't have listed? Let us know!",
      icon: DocumentArrowUpIcon,
      href: '/wheretoeat/recommend-a-place',
    },
  ].filter((card) => card.href !== undefined),
);
</script>

<template>
  <div class="grid grid-cols-1 gap-4 xs:grid-cols-2 xmd:grid-cols-1">
    <Link
      v-for="card in linkCards"
      :key="card.title"
      :href="card.href as string"
      prefetch
      class="flex cursor-pointer flex-col gap-2 rounded-sm bg-linear-to-br from-primary/90 to-primary-light/90 p-4 shadow-lg transition duration-500 sm:hover:from-primary/95 sm:hover:to-primary-light/95"
    >
      <div class="flex items-center gap-3">
        <component
          :is="card.icon"
          class="size-8 shrink-0"
        />

        <div class="text-lg font-semibold lg:text-xl">
          {{ card.title }}
        </div>
      </div>

      <p
        class="text-sm lg:text-base"
        v-text="card.description"
      />
    </Link>
  </div>
</template>
