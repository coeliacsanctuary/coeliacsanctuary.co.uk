<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import {
  CollectionDisplayType,
  EateryCollectionItem,
} from '@/types/CollectionTypes';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { pluralise } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';
import { computed } from 'vue';

const props = defineProps<{
  item: EateryCollectionItem;
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
        <StaticMap
          map-classes="aspect-[1200/630]"
          :lat="item.location.lat"
          :lng="item.location.lng"
          :title="item.name"
          :can-expand="false"
          lazy
        />
      </Link>
    </div>

    <div
      class="mt-4 flex flex-1 flex-col gap-3"
      :class="{ 'md:mt-0': isList }"
    >
      <div class="flex flex-col space-y-1">
        <Link
          :href="item.link"
          prefetch
        >
          <h3
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="item.name"
          />
        </Link>

        <p
          class="font-semibold md:text-lg"
          v-text="item.full_location"
        />

        <p
          class="text-sm text-grey-dark"
          v-text="item.location.address"
        />
      </div>

      <div class="flex flex-1">
        <p
          class="prose max-w-none md:prose-lg"
          v-html="item.description"
        />
      </div>

      <div
        class="flex items-end justify-between gap-3"
        :class="{ '-mx-4 -mb-4 bg-primary-lightest/60 p-4': !isList }"
      >
        <div
          v-if="item.reviews.number > 0"
          class="flex flex-col space-y-2"
        >
          <StarRating
            :rating="item.reviews.average"
            show-all
          />

          <span class="text-xs text-grey-dark">
            <strong>{{ item.reviews.average }} stars</strong> from
            <strong>
              {{ item.reviews.number }}
              {{ pluralise('review', item.reviews.number) }}
            </strong>
          </span>
        </div>

        <span
          class="ml-auto rounded-lg bg-primary-light/50 px-3 py-1.5 text-sm leading-none font-semibold"
          v-text="'Eatery'"
        />
      </div>
    </div>
  </Card>
</template>
