<script lang="ts" setup>
import { RecipePage } from '@/types/RecipeTypes';

withDefaults(
  defineProps<{
    nutrition: RecipePage['nutrition'];
    direction?: 'vertical' | 'horizontal';
  }>(),
  { direction: 'horizontal' },
);
</script>

<template>
  <div
    class="text-xs xs:text-sm border-2 border-primary rounded-lg overflow-hidden w-fit"
  >
    <div
      class="grid divide-x-2 divide-primary border-primary bg-primary-light/50 font-semibold"
      :class="{
        'grid-cols-6 text-center *:p-1': direction === 'horizontal',
        'grid-cols-4 divide-y-2 *:p-2': direction === 'vertical',
      }"
    >
      <div
        class="xs:hidden"
        :class="{ 'col-span-3': direction === 'vertical' }"
      >
        Cals
      </div>
      <div
        class="hidden xs:block"
        :class="{ 'col-span-3': direction === 'vertical' }"
      >
        Calories
      </div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.calories }}
      </div>

      <div
        class=""
        :class="{ 'col-span-3': direction === 'vertical' }"
      >
        Carbs
      </div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.carbs }}<sub>g</sub>
      </div>

      <div
        class=""
        :class="{ 'col-span-3': direction === 'vertical' }"
      >
        Fibre
      </div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.fibre }}<sub>g</sub>
      </div>

      <div
        class=""
        :class="{ 'col-span-3': direction === 'vertical' }"
      >
        Fat
      </div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.fat }}<sub>g</sub>
      </div>

      <div :class="{ 'col-span-3': direction === 'vertical' }">Sugar</div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.sugar }}<sub>g</sub>
      </div>

      <div
        class=""
        :class="{
          '!border-r-0': direction === 'horizontal',
          '!border-b-0 col-span-3': direction === 'vertical',
        }"
      >
        Protein
      </div>
      <div
        class="bg-white"
        :class="{
          'row-start-2': direction === 'horizontal',
          '!border-r-0': direction === 'vertical',
        }"
      >
        {{ nutrition.protein }}<sub>g</sub>
      </div>
    </div>
  </div>
</template>
