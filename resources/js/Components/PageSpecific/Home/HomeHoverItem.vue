<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { HomeHoverItem as HomeHoverItemType } from '@/types/Types';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';

defineProps<{ item: HomeHoverItemType }>();
</script>

<template>
  <div class="group/child h-full w-full">
    <Card
      :shadow="false"
      class="transform transition duration-500 sm:scale-95 sm:group-hover:opacity-50 sm:group-hover/child:scale-105 sm:group-hover/child:opacity-100!"
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
          class="sm:text-md p-2 text-center text-lg font-semibold transition xs:max-sm:text-xl sm:group-hover/child:text-primary-dark"
          v-text="item.title"
        />
      </Link>
    </Card>
  </div>
</template>
