<script lang="ts" setup>
import {
  CollectionItem,
  BlogCollectionItem as BlogCollectionItemType,
  RecipeCollectionItem as RecipeCollectionItemType,
  EateryCollectionItem as EateryCollectionItemType,
} from '@/types/CollectionTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';
import BlogCollectionItem from '@/Components/PageSpecific/Collections/Items/BlogCollectionItem.vue';
import RecipeCollectionItem from '@/Components/PageSpecific/Collections/Items/RecipeCollectionItem.vue';
import EateryCollectionItem from '@/Components/PageSpecific/Collections/Items/EateryCollectionItem.vue';

const props = defineProps<{ item: CollectionItem }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CollectionItemCard',
  {
    title: props.item?.title ?? props.item?.name ?? '',
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

    <EateryCollectionItem
      v-if="item.type === 'Eatery' || item.type === 'NationwideBranch'"
      :item="item as EateryCollectionItemType"
    />
  </div>
</template>
