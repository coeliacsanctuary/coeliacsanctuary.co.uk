<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { CollectionGroup } from '@/types/CollectionTypes';
import CollectionItemCard from '@/Components/PageSpecific/Collections/CollectionItemCard.vue';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{ group: CollectionGroup }>();
</script>

<template>
  <Card
    v-if="group.title || group.body"
    class="flex flex-col"
  >
    <SubHeading v-if="group.title">
      {{ group.title }}
    </SubHeading>

    <article
      v-if="group.body"
      class="prose prose-lg max-w-none md:prose-xl"
      :class="{ 'mt-4': !!group.title }"
      v-html="group.body"
    />
  </Card>

  <CollectionItemCard
    v-for="item in group.items"
    :key="item.title"
    :item="item"
  />
</template>
