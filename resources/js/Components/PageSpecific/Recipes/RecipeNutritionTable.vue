<script lang="ts" setup>
import { RecipePage } from '@/types/RecipeTypes';

withDefaults(
  defineProps<{
    nutrition: RecipePage['nutrition'];
    direction: 'vertical' | 'horizontal';
  }>(),
  { direction: 'horizontal' },
);
</script>

<template>
  <div class="flex text-xs xs:max-sm:text-sm sm:text-base">
    <div class="grid grid-rows-2 leading-none">
      <div
        class="grid grid-cols-6 divide-x-2 divide-primary border-b-2 border-primary bg-primary-light/50 font-semibold"
      >
        <div class="p-2 xs:hidden">Cals</div>
        <div class="hidden border-l-transparent! p-2 xs:block">Calories</div>
        <div class="p-2">Carbs</div>
        <div class="p-2">Fibre</div>
        <div class="p-2">Fat</div>
        <div class="p-2">Sugar</div>
        <div class="p-2">Protein</div>
      </div>

      <div
        class="grid grid-cols-6 divide-x-2 divide-primary border-b-2 border-primary"
      >
        <div class="p-2">
          {{ nutrition.calories }}
        </div>
        <div class="p-2">{{ nutrition.carbs }}<sub>g</sub></div>
        <div class="p-2">{{ nutrition.fibre }}<sub>g</sub></div>
        <div class="p-2">{{ nutrition.fat }}<sub>g</sub></div>
        <div class="p-2">{{ nutrition.sugar }}<sub>g</sub></div>
        <div class="p-2">{{ nutrition.protein }}<sub>g</sub></div>
      </div>
    </div>
  </div>
</template>
