<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Paginator from '@/Components/Paginator.vue';
import { router } from '@inertiajs/vue3';
import { Ref, ref, toRef } from 'vue';
import RecipeDetailCard from '@/Components/PageSpecific/Recipes/RecipeDetailCard.vue';
import RecipeListFilterCard, {
  RecipeFilterOption,
} from '@/Components/PageSpecific/Recipes/RecipeListFilterCard.vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import {
  RecipeDetailCard as RecipeDetailCardType,
  RecipeFeature,
  RecipeFreeFrom,
  RecipeMeal,
  RecipeSetFilters,
} from '@/types/RecipeTypes';

const props = defineProps<{
  recipes: PaginatedResponse<RecipeDetailCardType>;
  features: RecipeFeature[];
  meals: RecipeMeal[];
  freeFrom: RecipeFreeFrom[];
  setFilters: RecipeSetFilters;
}>();

const page = ref(1);
const selectedFeatures: Ref<string[]> = toRef(props.setFilters, 'features');
const selectedMeals: Ref<string[]> = ref(props.setFilters.meals);
const selectedAllergens: Ref<string[]> = ref(props.setFilters.freeFrom);

const refreshPage = (preserveScroll = true) => {
  router.get(
    '/recipe',
    {
      ...(page.value > 1 ? { page: page.value } : undefined),
      ...(selectedFeatures.value.length > 0
        ? { features: selectedFeatures.value.join() }
        : undefined),
      ...(selectedMeals.value.length > 0
        ? { meals: selectedMeals.value.join() }
        : undefined),
      ...(selectedAllergens.value.length > 0
        ? { freeFrom: selectedAllergens.value.join() }
        : undefined),
    },
    {
      only: ['recipes', 'features', 'meals', 'freeFrom', 'setFilters'],
      preserveState: true,
      preserveScroll,
    },
  );
};

const gotoPage = (p: number) => {
  page.value = p;

  refreshPage(false);
};

const featureOptions = (): RecipeFilterOption[] =>
  props.features.map((feature) => ({
    value: feature.slug,
    label: feature.feature,
    recipeCount: feature.recipes_count,
    disabled: feature.recipes_count === 0,
  }));

const selectFeature = (features: string[]): void => {
  selectedFeatures.value = features;

  page.value = 1;

  refreshPage();
};

const mealOptions = (): RecipeFilterOption[] =>
  props.meals.map((meal) => ({
    value: meal.slug,
    label: meal.meal,
    recipeCount: meal.recipes_count,
    disabled: meal.recipes_count === 0,
  }));

const selectMeal = (meals: string[]): void => {
  selectedMeals.value = meals;

  page.value = 1;

  refreshPage();
};

const freeFromOptions = (): RecipeFilterOption[] =>
  props.freeFrom.map((freeFrom) => ({
    value: freeFrom.slug,
    label: freeFrom.allergen,
    recipeCount: freeFrom.recipes_count,
    disabled: freeFrom.recipes_count === 0,
  }));

const selectAllergen = (freeFrom: string[]): void => {
  selectedAllergens.value = freeFrom;

  page.value = 1;

  refreshPage();
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Coeliac Sanctuary Recipes </Heading>

    <p class="prose max-w-none md:prose-lg">
      Why not check out some of our fabulous, gluten free, coeliac recipes! All
      of our recipes are tried and tested by us, and as much as we can, we will
      always use simple, easy to get ingredients, readily available in most
      supermarkets, so anyone can make them at home!
    </p>

    <div class="grid gap-3 md:grid-cols-3">
      <RecipeListFilterCard
        :current-options="selectedFeatures"
        :options="featureOptions()"
        label="Feature"
        @changed="selectFeature"
        class="z-50"
      />

      <RecipeListFilterCard
        :current-options="selectedMeals"
        :options="mealOptions()"
        label="Meals"
        @changed="selectMeal"
        class="z-49"
      />

      <RecipeListFilterCard
        :current-options="selectedAllergens"
        :options="freeFromOptions()"
        label="Free From"
        @changed="selectAllergen"
        class="z-48"
      />
    </div>

    <Paginator
      v-if="recipes.meta.last_page > 1"
      :current="recipes.meta.current_page"
      :to="recipes.meta.last_page"
      @change="gotoPage"
    />
  </Card>

  <div class="grid gap-8 sm:max-xl:grid-cols-2 sm:gap-0 xl:grid-cols-3">
    <template v-if="recipes.data.length">
      <RecipeDetailCard
        v-for="recipe in recipes.data"
        :key="recipe.link"
        :recipe="recipe"
        class="transition-duration-500 transition sm:scale-95 sm:hover:scale-100 sm:hover:shadow-lg"
      />
    </template>

    <Card
      v-else
      class="sm:col-span-3"
    >
      <p class="text-center text-xl">
        Sorry, we can't find any recipes using the options you've provided...
      </p>
    </Card>
  </div>

  <Paginator
    v-if="recipes.meta.last_page > 1"
    :current="recipes.meta.current_page"
    :to="recipes.meta.last_page"
    @change="gotoPage"
  />
</template>
