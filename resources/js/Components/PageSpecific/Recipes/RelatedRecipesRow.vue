<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { RelatedRecipeCard } from '@/types/RecipeTypes';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import SnapScrollRow from '@/Components/SnapScrollRow.vue';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';

defineProps<{ recipes: RelatedRecipeCard[] }>();
</script>

<template>
  <Card class="space-y-4">
    <SubHeading classes="text-primary-dark">Related Recipes</SubHeading>

    <SnapScrollRow
      :items="recipes"
      carousel
      item-classes="w-[75%] shrink-0 snap-start sm:w-[42%] lg:w-[28%]"
      :label="(recipe) => `Show ${recipe.title}`"
    >
      <template #default="{ item, itemClasses }">
        <Card
          class="group overflow-hidden !rounded-lg"
          :class="itemClasses"
          no-padding
        >
          <Link
            :href="item.link"
            class="flex h-full flex-col justify-between"
          >
            <div class="overflow-hidden">
              <RecipeSquareImage
                :alt="item.header_image_alt_text ?? item.title"
                :src="item.image"
                class="transition duration-500 group-hover:scale-105"
              />
            </div>

            <div
              class="flex min-h-16 w-full items-center justify-between gap-2 bg-primary-light/80 p-2 shadow"
            >
              <h3
                class="line-clamp-2 text-base font-semibold"
                v-text="item.title"
              />

              <ArrowRightIcon
                class="size-4 shrink-0 group-hover:animate-ping"
              />
            </div>
          </Link>
        </Card>
      </template>
    </SnapScrollRow>
  </Card>
</template>
