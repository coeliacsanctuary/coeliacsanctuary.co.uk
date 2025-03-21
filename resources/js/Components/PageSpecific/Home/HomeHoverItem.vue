<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { HomeHoverItem as HomeHoverItemType } from '@/types/Types';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';

defineProps<{ item: HomeHoverItemType }>();
</script>

<template>
  <Card
    :shadow="false"
    class="sm:scale-95 transform transition duration-500 sm:hover:scale-105 sm:hover:opacity-100! sm:group-hover:opacity-50"
  >
    <Link
      :href="item.link"
      class="group -m-4"
      prefetch
    >
      <img
        v-if="
          item.type !== 'Recipe' ||
          (item.type === 'Recipe' && item.square_image)
        "
        :alt="item.title"
        :src="item.image"
        loading="lazy"
      />
      <RecipeSquareImage
        v-else
        :alt="item.title"
        :src="item.image"
      />

      <h2
        class="p-2 text-center text-lg font-semibold transition sm:group-hover:text-primary-dark xs:max-sm:text-xl sm:text-md"
        v-text="item.title"
      />
    </Link>
  </Card>
</template>
