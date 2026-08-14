<script lang="ts" setup>
import { RecipeIngredientGroup } from '@/types/RecipeTypes';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{ groups: RecipeIngredientGroup[] }>();
</script>

<template>
  <div class="flex flex-col">
    <SubHeading classes="text-primary-dark">Ingredients</SubHeading>

    <div
      v-for="(group, groupIndex) in groups"
      :key="groupIndex"
      class="mt-5 flex flex-col"
    >
      <h3
        v-if="group.heading"
        class="mb-2 font-coeliac text-xl font-semibold text-grey-darkest"
        v-text="group.heading"
      />

      <ul class="divide-y divide-grey-off-light">
        <li
          v-for="item in group.items"
          :key="item"
          class="flex items-center gap-3 py-2"
        >
          <span
            class="size-5 shrink-0 rounded border-2 border-primary"
            aria-hidden="true"
          />

          <span
            class="leading-snug [&>a]:font-semibold [&>a]:text-primary-darkest"
            v-html="item"
          />
        </li>
      </ul>
    </div>
  </div>
</template>
