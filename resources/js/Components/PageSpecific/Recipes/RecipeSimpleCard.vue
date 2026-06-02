<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { RecipeDetailCard } from '@/types/RecipeTypes';

defineProps<{ recipe: RecipeDetailCard }>();
</script>

<template>
  <Card
    :shadow="false"
    class="scale-95 transform transition duration-500 hover:scale-105 hover:opacity-100!"
  >
    <Link
      :href="recipe.link"
      class="group -m-4"
    >
      <img
        :alt="recipe.header_image_alt_text ?? recipe.title"
        :src="recipe.image"
        loading="lazy"
      />

      <h2
        class="p-2 text-center text-base font-semibold transition group-hover:text-primary-dark"
        v-text="recipe.title"
      />
    </Link>
  </Card>
</template>
