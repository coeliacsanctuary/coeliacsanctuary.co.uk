<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { CollectionDetailCard } from '@/types/CollectionTypes';

defineProps<{ collection: CollectionDetailCard }>();
</script>

<template>
  <Card>
    <div class="group flex-1">
      <Link
        :href="collection.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :alt="collection.title"
          :src="collection.image"
          loading="lazy"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link :href="collection.link">
          <h2
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark"
            v-text="collection.title"
          />
        </Link>

        <div class="flex flex-1">
          <p v-text="collection.description" />
        </div>
      </div>
    </div>

    <div class="-m-4 mt-4 flex flex-col bg-grey-light p-4 text-sm shadow-inner">
      <p>Last updated on {{ collection.date }}</p>
      <p>{{ collection.number_of_items }} items in this collection.</p>
    </div>
  </Card>
</template>
