<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';
import { EateryCollectionCard } from '@/types/EatingOutCollectionTypes';

const props = defineProps<{ collection: EateryCollectionCard }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryCollectionCard',
  {
    title: props.collection.title,
  },
);
</script>

<template>
  <Card ref="card">
    <div class="group flex-1">
      <Link
        :href="collection.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :alt="collection.title"
          :src="collection.image"
          loading="lazy"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link :href="collection.link">
          <h2
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="collection.title"
          />
        </Link>

        <div class="flex flex-1">
          <p
            class="prose max-w-none md:prose-lg"
            v-text="collection.description"
          />
        </div>
      </div>
    </div>

    <div class="-m-4 mt-4 flex flex-col bg-grey-light p-4 text-sm shadow-inner">
      <div class="flex justify-between">
        <div>
          <template v-if="collection.eateries_count">
            {{ collection.eateries_count }} locations
          </template>
        </div>
        <div>Added on {{ collection.date }}</div>
      </div>
    </div>
  </Card>
</template>
