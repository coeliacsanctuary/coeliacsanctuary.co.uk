<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { CollectionDetailCard } from '@/types/CollectionTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { computed, useTemplateRef } from 'vue';
import { pluralise } from '@/helpers';

const props = withDefaults(
  defineProps<{ collection: CollectionDetailCard; eager?: boolean }>(),
  { eager: false },
);

const itemSummary = computed(() => {
  const { recipes, blogs, eateries } = props.collection.items;

  return [
    recipes ? `${recipes} ${pluralise('recipe', recipes)}` : null,
    blogs ? `${blogs} ${pluralise('blog', blogs)}` : null,
    eateries
      ? `${eateries} ${eateries === 1 ? 'place to eat' : 'places to eat'}`
      : null,
  ]
    .filter(Boolean)
    .join(' · ');
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CollectionDetailCard',
  {
    title: props.collection.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="overflow-hidden"
  >
    <div class="group flex-1">
      <Link
        :href="collection.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :alt="collection.header_image_alt_text ?? collection.title"
          :src="collection.image"
          :loading="eager ? 'eager' : 'lazy'"
          :fetchpriority="eager ? 'high' : 'auto'"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link
          :href="collection.link"
          prefetch
        >
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

    <div
      class="-mx-4 mt-4 -mb-4 flex flex-col space-y-3 bg-primary-lightest/60 p-4"
    >
      <span
        v-if="itemSummary"
        class="text-sm font-semibold text-grey-darker"
        v-text="itemSummary"
      />

      <span class="text-xs text-grey-dark italic">
        Last updated {{ collection.date }}
      </span>
    </div>
  </Card>
</template>
