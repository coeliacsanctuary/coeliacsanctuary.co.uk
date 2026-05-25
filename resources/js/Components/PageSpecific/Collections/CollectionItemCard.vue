<script lang="ts" setup>
import {
  CollectionItem,
  BlogCollectionItem as BlogCollectionItemType,
  RecipeCollectionItem as RecipeCollectionItemType,
} from '@/types/CollectionTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';
import BlogCollectionItem from '@/Components/PageSpecific/Collections/Items/BlogCollectionItem.vue';
import RecipeCollectionItem from '@/Components/PageSpecific/Collections/Items/RecipeCollectionItem.vue';

const props = defineProps<{ item: CollectionItem }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CollectionItemCard',
  {
    title: props.item.title,
    type: props.item.type,
  },
);
</script>

<template>
  <div ref="card">
    <BlogCollectionItem
      v-if="item.type === 'Blog'"
      :item="item as BlogCollectionItemType"
    />

    <RecipeCollectionItem
      v-if="item.type === 'Recipe'"
      :item="item as RecipeCollectionItemType"
    />
  </div>
</template>
