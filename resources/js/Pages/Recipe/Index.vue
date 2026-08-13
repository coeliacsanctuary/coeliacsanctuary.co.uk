<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Loader from '@/Components/Loader.vue';
import Paginator from '@/Components/Paginator.vue';
import { router } from '@inertiajs/vue3';
import { Component, computed, ref } from 'vue';
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
import { RssIcon } from '@heroicons/vue/20/solid';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowPathIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import useBrowser from '@/composables/useBrowser';
import { pluralise } from '@/helpers';

type FilterKey = keyof RecipeSetFilters;

type AppliedFilter = {
  key: FilterKey;
  value: string;
  label: string;
};

const props = defineProps<{
  recipes: PaginatedResponse<RecipeDetailCardType>;
  features: RecipeFeature[];
  meals: RecipeMeal[];
  freeFrom: RecipeFreeFrom[];
  setFilters: RecipeSetFilters;
}>();

const isLoading = ref(false);

const refreshPage = (
  overrides: Partial<RecipeSetFilters> & { page?: number } = {},
  preserveScroll = true,
) => {
  const filters = { ...props.setFilters, ...overrides };
  const page = overrides.page ?? props.recipes.meta.current_page;

  router.get(
    useBrowser().currentPath(),
    {
      ...(page > 1 ? { page } : undefined),
      ...(filters.features.length > 0
        ? { features: filters.features.join() }
        : undefined),
      ...(filters.meals.length > 0
        ? { meals: filters.meals.join() }
        : undefined),
      ...(filters.freeFrom.length > 0
        ? { freeFrom: filters.freeFrom.join() }
        : undefined),
    },
    {
      only: ['recipes', 'features', 'meals', 'freeFrom', 'setFilters'],
      preserveState: true,
      preserveScroll,
      onStart: () => (isLoading.value = true),
      onFinish: () => (isLoading.value = false),
    },
  );
};

const gotoPage = (page: number) => refreshPage({ page }, false);

const featureOptions = (): RecipeFilterOption[] =>
  props.features.map((feature) => ({
    value: feature.slug,
    label: feature.feature,
    recipeCount: feature.recipes_count,
    disabled: feature.recipes_count === 0,
  }));

const mealOptions = (): RecipeFilterOption[] =>
  props.meals.map((meal) => ({
    value: meal.slug,
    label: meal.meal,
    recipeCount: meal.recipes_count,
    disabled: meal.recipes_count === 0,
  }));

const freeFromOptions = (): RecipeFilterOption[] =>
  props.freeFrom.map((freeFrom) => ({
    value: freeFrom.slug,
    label: freeFrom.allergen,
    recipeCount: freeFrom.recipes_count,
    disabled: freeFrom.recipes_count === 0,
  }));

const selectFilter = (key: FilterKey, values: string[]): void => {
  const overrides: Partial<RecipeSetFilters> = {};

  overrides[key] = values;

  refreshPage({ ...overrides, page: 1 });
};

const removeFilter = (filter: AppliedFilter): void =>
  selectFilter(
    filter.key,
    props.setFilters[filter.key].filter((value) => value !== filter.value),
  );

const resetFilters = () =>
  refreshPage({ features: [], meals: [], freeFrom: [], page: 1 });

const filterKeys: FilterKey[] = ['features', 'meals', 'freeFrom'];

const filterLabels = computed(
  (): Record<FilterKey, Record<string, string>> => ({
    features: Object.fromEntries(
      props.features.map((feature) => [feature.slug, feature.feature]),
    ),
    meals: Object.fromEntries(
      props.meals.map((meal) => [meal.slug, meal.meal]),
    ),
    freeFrom: Object.fromEntries(
      props.freeFrom.map((freeFrom) => [freeFrom.slug, freeFrom.allergen]),
    ),
  }),
);

const appliedFilters = computed((): AppliedFilter[] =>
  filterKeys.flatMap((key) =>
    props.setFilters[key].map((value) => ({
      key,
      value,
      label: filterLabels.value[key][value] ?? value,
    })),
  ),
);

const isFiltered = computed(() => appliedFilters.value.length > 0);

const resultSummary = computed(() => {
  const { from, to, total } = props.recipes.meta;

  if (total === 0) {
    return 'No recipes found';
  }

  if (total <= props.recipes.meta.per_page) {
    return `Showing ${total} ${pluralise('recipe', total)}`;
  }

  return `Showing ${from} - ${to} of ${total} recipes`;
});
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :custom-link="{
        label: 'RSS Feed',
        href: '/recipe/feed',
        classes: 'font-semibold text-rss hover:text-black transition',
        newTab: true,
        position: 'bottom',
        direction: 'center',
        icon: RssIcon as Component,
        iconPosition: 'left',
      }"
    >
      Coeliac Sanctuary Recipes
    </Heading>

    <p class="prose max-w-none md:prose-lg">
      Why not check out some of our fabulous, gluten free, coeliac recipes! All
      of our recipes are tried and tested by us, and as much as we can, we will
      always use simple, easy to get ingredients, readily available in most
      supermarkets, so anyone can make them at home!
    </p>

    <div class="grid gap-3 md:grid-cols-3">
      <RecipeListFilterCard
        :current-options="setFilters.features"
        :options="featureOptions()"
        label="Feature"
        @changed="selectFilter('features', $event)"
      />

      <RecipeListFilterCard
        :current-options="setFilters.meals"
        :options="mealOptions()"
        label="Meals"
        @changed="selectFilter('meals', $event)"
      />

      <RecipeListFilterCard
        :current-options="setFilters.freeFrom"
        :options="freeFromOptions()"
        label="Free From"
        @changed="selectFilter('freeFrom', $event)"
      />
    </div>

    <div
      v-if="isFiltered"
      class="flex flex-wrap items-center gap-2"
    >
      <button
        v-for="filter in appliedFilters"
        :key="`${filter.key}-${filter.value}`"
        type="button"
        class="group inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-secondary bg-secondary/50 py-1 pr-2 pl-3 text-sm font-semibold transition hover:bg-secondary"
        :aria-label="`Remove the ${filter.label} filter`"
        @click="removeFilter(filter)"
      >
        <span v-text="filter.label" />

        <XMarkIcon
          class="size-4 text-grey-dark transition group-hover:text-black"
        />
      </button>

      <CoeliacButton
        label="Reset Filters"
        :icon="ArrowPathIcon"
        bold
        size="sm"
        theme="ghost"
        as="button"
        @click="resetFilters()"
      />
    </div>

    <p
      class="text-sm font-semibold text-grey-dark"
      v-text="resultSummary"
    />
  </Card>

  <div class="relative">
    <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
      <template v-if="recipes.data.length">
        <RecipeDetailCard
          v-for="(recipe, index) in recipes.data"
          :key="recipe.link"
          :recipe="recipe"
          :eager="index < 3"
          class="transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-lg"
        />
      </template>

      <Card
        v-else
        class="col-span-full items-center space-y-4"
      >
        <p class="text-center text-xl">
          Sorry, we can't find any recipes using the options you've provided...
        </p>

        <CoeliacButton
          v-if="isFiltered"
          label="Reset Filters"
          :icon="ArrowPathIcon"
          bold
          size="lg"
          theme="light"
          as="button"
          @click="resetFilters()"
        />
      </Card>
    </div>

    <Loader
      :display="isLoading"
      color="primary"
      size="size-12"
      fade
      blur
    />
  </div>

  <Paginator
    v-if="recipes.meta.last_page > 1"
    :current="recipes.meta.current_page"
    :to="recipes.meta.last_page"
    @change="gotoPage"
  />
</template>
