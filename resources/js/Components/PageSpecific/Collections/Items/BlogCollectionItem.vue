<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import {
  BlogCollectionItem,
  CollectionDisplayType,
} from '@/types/CollectionTypes';
import { computed } from 'vue';

const props = defineProps<{
  item: BlogCollectionItem;
  displayType: CollectionDisplayType;
}>();

const isList = computed(() => props.displayType === 'list');
</script>

<template>
  <Card
    class="group flex-1 overflow-hidden"
    :class="{ 'md:flex-row md:gap-4': isList }"
  >
    <div :class="{ 'md:max-w-3xs md:min-w-1/4 md:flex-none': isList }">
      <Link
        :href="item.link"
        class="mb-0 flex flex-col"
        :class="{ '-m-4': !isList }"
        prefetch
      >
        <img
          :alt="item.header_image_alt_text ?? item.title"
          :src="item.image"
          loading="lazy"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover"
        />
      </Link>
    </div>

    <div
      class="mt-4 flex flex-1 flex-col gap-3"
      :class="{ 'md:mt-0': isList }"
    >
      <Link
        :href="item.link"
        prefetch
      >
        <h3
          class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
          v-text="item.title"
        />
      </Link>

      <div class="flex flex-1">
        <p
          class="prose max-w-none md:prose-lg"
          v-text="item.description"
        />
      </div>

      <div
        class="flex items-end justify-between gap-3"
        :class="{ '-mx-4 -mb-4 bg-primary-lightest/60 p-4': !isList }"
      >
        <span class="text-xs text-grey-dark italic">
          Added on {{ item.date }}
        </span>

        <span
          class="rounded-lg bg-primary-light/50 px-3 py-1.5 text-sm leading-none font-semibold"
          v-text="item.type"
        />
      </div>
    </div>
  </Card>
</template>
