<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { CollectionPage } from '@/types/CollectionTypes';
import CollectionItemCard from '@/Components/PageSpecific/Collections/CollectionItemCard.vue';
import GoogleAd from '@/Components/GoogleAd.vue';

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

    <GoogleAd code="7206823714" />
  </Card>

  <CollectionItemCard
    v-for="item in collection.items"
    :key="item.title"
    :item="item"
  />
</template>
