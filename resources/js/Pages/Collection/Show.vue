<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { CollectionPage } from '@/types/CollectionTypes';
import CollectionGroupCard from '@/Components/PageSpecific/Collections/CollectionGroupCard.vue';

defineProps<{ collection: CollectionPage }>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        href: '/collection',
        label: 'Back to all collections.',
      }"
    >
      {{ collection.title }}
    </Heading>

    <div
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="collection.description"
    />

    <div class="-m-4 -mb-4! bg-grey-light p-4 shadow-inner">
      <p v-if="collection.updated">
        <span class="font-semibold">Last updated</span> {{ collection.updated }}
      </p>
      <p><span class="font-semibold">Added</span> {{ collection.published }}</p>
    </div>
  </Card>

  <Card no-padding>
    <img
      :alt="collection.title"
      :src="collection.image"
      loading="lazy"
    />
  </Card>

  <Card
    v-if="collection.body"
    class="space-y-3"
  >
    <article
      class="prose prose-lg max-w-none md:prose-xl"
      v-html="collection.body"
    />
  </Card>

  <CollectionGroupCard
    v-for="group in collection.groups"
    :key="group.title"
    :group="group"
  />
</template>
