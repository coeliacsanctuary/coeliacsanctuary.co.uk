<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CollectionDisplayType,
  CollectionGroup,
} from '@/types/CollectionTypes';
import SubHeading from '@/Components/SubHeading.vue';
import RenderedString from '@/Components/RenderedString.vue';
import CollectionItems from '@/Components/PageSpecific/Collections/CollectionItems.vue';

withDefaults(
  defineProps<{
    group: CollectionGroup;
    displayType: CollectionDisplayType;
    wrapped?: boolean;
  }>(),
  { wrapped: true },
);
</script>

<template>
  <Card
    v-if="wrapped"
    class="gap-4"
  >
    <SubHeading v-if="group.title">
      {{ group.title }}
    </SubHeading>

    <article
      v-if="group.body"
      class="prose prose-lg max-w-none md:prose-xl"
    >
      <RenderedString :content="group.body" />
    </article>

    <CollectionItems
      v-if="group.items.length"
      :items="group.items"
      :display-type="displayType"
    />
  </Card>

  <CollectionItems
    v-else
    :items="group.items"
    :display-type="displayType"
  />
</template>
