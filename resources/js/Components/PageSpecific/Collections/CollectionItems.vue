<script lang="ts" setup>
import { CollectionDisplayType, CollectionItem } from '@/types/CollectionTypes';
import CollectionItemCard from '@/Components/PageSpecific/Collections/CollectionItemCard.vue';

defineProps<{ items: CollectionItem[]; displayType: CollectionDisplayType }>();
</script>

<template>
  <div
    class="gap-4"
    :class="
      displayType === 'list'
        ? 'flex flex-col'
        : 'grid sm:grid-cols-2 xl:grid-cols-3'
    "
  >
    <CollectionItemCard
      v-for="item in items"
      :key="item.link"
      :item="item"
      :display-type="displayType"
    />
  </div>
</template>
