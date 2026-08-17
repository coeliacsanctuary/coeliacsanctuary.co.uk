<script lang="ts" setup>
import { HomepageCollection } from '@/types/CollectionTypes';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import HomeContentTile from '@/Components/PageSpecific/Home/HomeContentTile.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { computed, useTemplateRef } from 'vue';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{ collection: HomepageCollection }>();

const collectionWrapperClasses = computed(() => {
  const base = ['grid', 'grid-cols-1', 'gap-4'];

  if (
    props.collection.items_to_display === 2 ||
    props.collection.items_to_display === 4 ||
    props.collection.items_to_display === 8
  ) {
    base.push('sm:grid-cols-2');
  }

  if (
    props.collection.items_to_display === 3 ||
    props.collection.items_to_display === 6
  ) {
    base.push('sm:grid-cols-3');
  }

  if (
    props.collection.items_to_display === 4 ||
    props.collection.items_to_display === 8
  ) {
    base.push('xmd:grid-cols-4');
  }

  return base;
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'Home/Collection',
  {
    collection: props.collection.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4 overflow-hidden"
  >
    <SubHeading
      as="h2"
      text-size="small"
    >
      {{ collection.title }}
    </SubHeading>

    <p
      class="prose max-w-none sm:prose-lg"
      v-html="collection.description"
    />

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <section :class="collectionWrapperClasses">
      <HomeContentTile
        v-for="item in collection.items"
        :key="item.link"
        :item="item"
        landscape
      />
    </section>

    <Link
      :href="collection.link"
      class="-mx-4 -mb-4 mt-4! flex items-center justify-center space-x-2 bg-primary-lightest/60 p-4 font-semibold transition hover:bg-primary-light/60"
      prefetch
    >
      <span>View more {{ collection.title }} items</span>

      <ArrowRightIcon class="size-4" />
    </Link>
  </Card>
</template>
