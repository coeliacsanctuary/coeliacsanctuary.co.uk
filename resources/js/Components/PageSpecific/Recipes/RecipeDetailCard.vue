<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';
import { RecipeDetailCard } from '@/types/RecipeTypes';

defineProps<{ recipe: RecipeDetailCard }>();
</script>

<template>
  <Card>
    <div class="group flex-1">
      <Link
        :href="recipe.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          v-if="recipe.square_image"
          :alt="recipe.title"
          :src="recipe.image"
          loading="lazy"
        />
        <RecipeSquareImage
          v-else
          :alt="recipe.title"
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

    <div class="-m-4 mt-4 flex flex-col bg-grey-light p-4 text-sm shadow-inner">
      <div class="flex flex-col space-y-4">
        <div
          v-if="recipe.features.length"
          class="flex flex-col"
        >
          <h4 class="font-semibold">This recipe is</h4>
          <ul class="flex flex-wrap gap-2 gap-y-1">
            <li
              v-for="feature in recipe.features"
              :key="feature.slug"
              class="after:content-[','] last:after:content-['']"
            >
              <Link
                :href="`/recipe?features=${feature.slug}`"
                class="font-semibold text-primary-dark transition hover:text-black"
              >
                {{ feature.feature }}
              </Link>
            </li>
          </ul>
        </div>

        <div class="flex flex-col">
          <span class="font-semibold">
            Makes {{ recipe.nutrition.servings }}
          </span>

          <span class="font-semibold">
            {{ recipe.nutrition.calories }} calories per
            {{ recipe.nutrition.portion_size }}
          </span>
        </div>
      </div>

      <div class="mt-4 flex justify-between">
        <div>Added on {{ recipe.date }}</div>
      </div>
    </div>
  </Card>
</template>
