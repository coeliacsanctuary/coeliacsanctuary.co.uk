<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { EateryCollectionPage } from '@/types/EatingOutCollectionTypes';

defineProps<{
  collection: EateryCollectionPage;
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        href: '/eating-out/collections',
        label: 'Back to all collections.',
      }"
    >
      {{ collection.title }}
    </Heading>

    <p
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-html="collection.description"
    />

    <div
      class="-m-4 -mb-4! flex flex-col space-y-4 bg-grey-light p-4 text-sm shadow-inner"
    >
      <div>
        <p v-if="collection.updated">Last updated {{ collection.updated }}</p>
        <p>Published {{ collection.published }}</p>
      </div>
    </div>
  </Card>

  <Card no-padding>
    <img
      :alt="collection.title"
      :src="collection.image"
      loading="lazy"
    />
  </Card>

  <Card>
    <div
      class="prose prose-lg max-w-none md:prose-xl"
      v-html="collection.body"
    />
  </Card>
</template>
