<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';
import { RecipeDetailCard } from '@/types/RecipeTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { computed, useTemplateRef } from 'vue';

const props = withDefaults(
  defineProps<{ recipe: RecipeDetailCard; eager?: boolean }>(),
  { eager: false },
);

const nutritionSummary = computed(() =>
  [
    props.recipe.nutrition.servings
      ? `Makes ${props.recipe.nutrition.servings}`
      : null,
    props.recipe.nutrition.calories
      ? `${props.recipe.nutrition.calories} calories per ${props.recipe.nutrition.portion_size}`
      : null,
  ]
    .filter(Boolean)
    .join(' · '),
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'RecipeDetailCard',
  {
    title: props.recipe.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="overflow-hidden"
  >
    <div class="group flex-1">
      <Link
        :href="recipe.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          v-if="recipe.square_image"
          :alt="recipe.header_image_alt_text ?? recipe.title"
          :src="recipe.image"
          :loading="eager ? 'eager' : 'lazy'"
          :fetchpriority="eager ? 'high' : 'auto'"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover"
        />
        <RecipeSquareImage
          v-else
          :alt="recipe.header_image_alt_text ?? recipe.title"
          :src="recipe.image"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link :href="recipe.link">
          <h2
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="recipe.title"
          />
        </Link>

        <div class="flex flex-1">
          <p
            class="prose max-w-none md:prose-lg"
            v-text="recipe.description"
          />
        </div>
      </div>
    </div>

    <div
      class="-mx-4 mt-4 -mb-4 flex flex-col space-y-3 bg-primary-lightest/60 p-4"
    >
      <ul
        v-if="recipe.features.length"
        class="flex flex-wrap gap-2"
      >
        <li
          v-for="feature in recipe.features"
          :key="feature.slug"
        >
          <Link
            :href="`/recipe?features=${feature.slug}`"
            class="inline-block rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-sm font-semibold transition hover:bg-secondary"
          >
            {{ feature.feature }}
          </Link>
        </li>
      </ul>

      <span
        v-if="nutritionSummary"
        class="text-sm font-semibold text-grey-darker"
        v-text="nutritionSummary"
      />

      <span class="text-xs text-grey-dark italic">
        Added on {{ recipe.date }}
      </span>
    </div>
  </Card>
</template>
