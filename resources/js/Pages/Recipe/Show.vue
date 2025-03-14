<script lang="ts" setup>
import { PaginatedResponse } from '@/types/GenericTypes';
import { ref, Ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { RecipePage } from '@/types/RecipeTypes';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Comments from '@/Components/PageSpecific/Shared/Comments.vue';
import { PrinterIcon } from '@heroicons/vue/20/solid';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';
import RecipeNutritionTable from '@/Components/PageSpecific/Recipes/RecipeNutritionTable.vue';
import { Page } from '@inertiajs/core';
import GoogleAd from '@/Components/GoogleAd.vue';
import SubHeading from '@/Components/SubHeading.vue';

const props = defineProps<{
  recipe: RecipePage;
  comments: PaginatedResponse<Comment>;
}>();

const allComments: Ref<PaginatedResponse<Comment>> = ref(props.comments);

const loadMoreComments = () => {
  if (!props.comments.links.next) {
    return;
  }

  router.get(
    props.comments.links.next,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      only: ['comments'],
      preserveUrl: true,
      onSuccess: (page: Page<{ comments?: PaginatedResponse<Comment> }>) => {
        if (page.props.comments) {
          allComments.value.data.push(...page.props.comments.data);
          allComments.value.links = page.props.comments.links;
          allComments.value.meta = page.props.comments.meta;
        }
      },
    },
  );
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        href: '/recipe',
        label: 'Back to all recipes...',
      }"
    >
      {{ recipe.title }}
    </Heading>

    <div
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="recipe.description"
    />

    <div
      class="flex flex-col space-y-2 xs:flex-row xs:justify-between xs:space-y-0 md:text-lg"
    >
      <div v-if="recipe.features.length">
        <h3 class="font-semibold text-grey-darkest">This recipe is...</h3>
        <ul class="flex flex-row flex-wrap gap-2 gap-y-1 leading-tight">
          <li
            v-for="feature in recipe.features"
            :key="feature.slug"
            class="after:content-[','] last:after:content-['']"
          >
            <Link
              :href="`/recipe?features=${feature.slug}`"
              class="font-semibold text-primary-dark hover:text-grey-darker"
            >
              {{ feature.feature }}
            </Link>
          </li>
        </ul>
      </div>

      <div v-if="recipe.allergens.length">
        <div class="w-full rounded-sm bg-red-light/10 p-3 pr-12">
          <h3 class="font-semibold text-grey-darkest">This recipe contains:</h3>
          <ul class="flex flex-row flex-wrap gap-2 gap-y-1 leading-tight">
            <li
              v-for="allergen in recipe.allergens"
              :key="allergen.slug"
              class="font-semibold text-primary-dark after:content-[','] last:after:content-['']"
              v-text="allergen.allergen"
            />
          </ul>
        </div>
      </div>
    </div>

    <div
      class="-m-4 -mb-4! flex justify-between bg-grey-light p-4 shadow-inner"
    >
      <div>
        <p v-if="recipe.updated">
          <span class="font-semibold">Last updated</span> {{ recipe.updated }}
        </p>
        <p><span class="font-semibold">Added</span> {{ recipe.published }}</p>
        <p>
          <span class="font-semibold">Recipe by</span>
          <span v-html="recipe.author" />
        </p>
      </div>

      <div>
        <a
          :href="recipe.print_url"
          target="_blank"
        >
          <PrinterIcon class="h-12 w-12" />
        </a>
      </div>
    </div>
  </Card>

  <Card no-padding>
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
  </Card>

  <Card v-if="recipe.featured_in.length">
    <h3 class="text-base font-semibold text-grey-darkest">
      This recipe was featured in
    </h3>

    <ul class="mt-2 flex flex-row flex-wrap text-sm leading-tight">
      <li
        v-for="collection in recipe.featured_in"
        :key="collection.link"
        class="after:content-[','] last:after:content-['']"
      >
        <Link
          :href="collection.link"
          class="font-semibold text-primary-dark hover:text-grey-darker"
        >
          {{ collection.title }}
        </Link>
      </li>
    </ul>
  </Card>

  <Card class="space-y-3 pb-0">
    <SubHeading classes="text-primary-dark">Ingredients</SubHeading>

    <div
      class="prose prose-lg max-w-none md:prose-xl"
      v-html="recipe.ingredients"
    />

    <ul class="-m-4 mt-4 border-t border-grey-off-light bg-grey-light p-4">
      <li>
        <strong class="font-semibold">Preparation Time:</strong>
        {{ recipe.timing.prep_time }}
      </li>
      <li>
        <strong class="font-semibold">Cooking Time:</strong>
        {{ recipe.timing.cook_time }}
      </li>
      <li>
        <strong class="font-semibold"
          >This recipe makes {{ recipe.nutrition.servings }}</strong
        >
      </li>
    </ul>
  </Card>

  <GoogleAd code="2137793897" />

  <Card class="space-y-3">
    <SubHeading classes="text-primary-dark">Method</SubHeading>

    <article
      class="prose prose-lg max-w-none md:prose-xl"
      v-html="recipe.method"
    />

    <h3 class="mb-2 mt-4 text-base font-semibold">
      Nutritional Information (Per {{ recipe.nutrition.portion_size }})
    </h3>

    <RecipeNutritionTable :nutrition="recipe.nutrition" />
  </Card>

  <Comments
    :id="recipe.id"
    :comments="allComments"
    module="recipe"
    @load-more="loadMoreComments"
  />
</template>
