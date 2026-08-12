<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { RecipePage } from '@/types/RecipeTypes';
import Icon from '@/Components/Icon.vue';

defineProps<{
  features: RecipePage['features'];
  allergens: RecipePage['allergens'];
}>();
</script>

<template>
  <div class="flex flex-col gap-4 xmd:flex-row xmd:gap-8">
    <div
      v-if="features.length"
      class="flex flex-col gap-2"
    >
      <h2 class="text-sm font-semibold tracking-wide text-primary-dark uppercase">
        This recipe is
      </h2>

      <ul class="flex flex-wrap gap-2">
        <li
          v-for="feature in features"
          :key="feature.slug"
        >
          <Link
            :href="`/recipe?features=${feature.slug}`"
            class="flex items-center gap-2 rounded-lg bg-primary/40 px-3 py-2 font-semibold transition hover:bg-primary"
          >
            <Icon
              :name="feature.slug"
              class="size-6 shrink-0 text-primary-dark"
            />

            {{ feature.feature }}
          </Link>
        </li>
      </ul>
    </div>

    <div
      v-if="allergens.length"
      class="flex flex-col gap-2"
    >
      <h2 class="text-sm font-semibold tracking-wide text-red-dark uppercase">
        Contains
      </h2>

      <ul class="flex flex-wrap gap-2">
        <li
          v-for="allergen in allergens"
          :key="allergen.slug"
          class="flex items-center gap-2 rounded-lg bg-red-dark/15 px-3 py-2 font-semibold text-grey-darkest"
        >
          <Icon
            :name="allergen.slug"
            class="size-6 shrink-0 text-red-dark"
          />

          {{ allergen.allergen }}
        </li>
      </ul>
    </div>
  </div>
</template>
