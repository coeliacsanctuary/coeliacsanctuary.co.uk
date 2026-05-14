<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { Link } from '@inertiajs/vue3';
import { FeaturedInCollection } from '@/types/CollectionTypes';

defineProps<{ collections: FeaturedInCollection[]; title: string }>();
</script>

<template>
  <Card class="flex flex-col space-y-3 lg:row-start-2">
    <SubHeading
      text-size="base"
      classes="!mb-3"
    >
      {{ title }}
    </SubHeading>

    <Card
      v-for="collection in collections"
      :key="collection.link"
      theme="primary-light"
      class="group overflow-hidden"
      no-padding
      :shadow="false"
      faded
    >
      <Link :href="collection.link">
        <img
          :src="collection.image"
          :alt="collection.title"
          class="scale-95 transform transition duration-500 group-hover:scale-100 group-hover:opacity-100 sm:opacity-80"
        />
      </Link>

      <div class="m-3">
        <Link
          :href="collection.link"
          class="font-coeliac text-2xl font-semibold text-black transition duration-500 group-hover:text-primary-darkest md:text-3xl lg:text-4xl"
        >
          {{ collection.title }}
        </Link>

        <p
          class="prose max-w-none"
          v-html="collection.description"
        />
      </div>
    </Card>
  </Card>
</template>
