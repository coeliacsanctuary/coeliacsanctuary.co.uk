<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { CollectionGroup } from '@/types/CollectionTypes';
import CollectionItemCard from '@/Components/PageSpecific/Collections/CollectionItemCard.vue';
import SubHeading from '@/Components/SubHeading.vue';
import RenderedString from '@/Components/RenderedString.vue';

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
    >
      <RenderedString :content="group.body" />
    </article>
  </Card>

  <CollectionItemCard
    v-for="item in group.items"
    :key="item.link"
    :item="item"
  />
</template>
