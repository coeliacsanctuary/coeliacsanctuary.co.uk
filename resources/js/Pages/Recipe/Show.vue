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
import Warning from '@/Components/Warning.vue';
import Info from '@/Components/Info.vue';
import useScreensize from '@/composables/useScreensize';

const props = defineProps<{
  recipe: RecipePage;
  comments: PaginatedResponse<Comment>;
  backLink: string;
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
        href: backLink,
        label: 'Back to all recipes.',
      }"
    >
      {{ recipe.title }}
    </Heading>

    <div
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="recipe.description"
    />

    <div
      class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:space-y-0 lg:space-x-4"
    >
      <Info
        v-if="recipe.features.length"
        class="lg:w-md"
      >
        <h3 class="mb-1 text-lg font-semibold text-grey-darkest">
          This recipe is...
        </h3>

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
      </Info>

      <Info
        class="lg:w-md"
        no-icon
        theme="light"
      >
        <ul class="">
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
      </Info>

      <Warning
        v-if="recipe.allergens.length"
        class="lg:w-md"
      >
        <h3 class="mb-1 text-lg font-semibold text-red-dark">
          This recipe contains:
        </h3>

        <ul class="flex flex-row flex-wrap gap-2 gap-y-1 leading-tight">
          <li
            v-for="allergen in recipe.allergens"
            :key="allergen.slug"
            class="font-semibold text-black after:content-[','] last:after:content-['']"
            v-text="allergen.allergen"
          />
        </ul>
      </Warning>
    </div>

    <div
      class="-m-4 !mt-4 -mb-4! flex justify-between bg-grey-light p-4 shadow-inner"
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

  <div
    class="relative flex flex-col space-y-3 lg:flex-row lg:space-y-0 lg:space-x-3"
  >
    <div
      class="space-y-3 lg:ml-3 lg:grid lg:w-[350px] lg:flex-shrink-0 lg:grid-cols-1 lg:self-start lg:overflow-auto"
    >
      <Card
        v-if="recipe.featured_in?.length"
        class="lg:row-start-2"
      >
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

      <Card>
        <SubHeading classes="text-primary-dark">Ingredients</SubHeading>

        <div
          class="prose prose-lg max-w-none md:prose-xl"
          v-html="recipe.ingredients"
        />
      </Card>

      <Card class="hidden lg:flex">
        <h3 class="mb-4 text-base font-semibold">
          Nutritional Information (Per {{ recipe.nutrition.portion_size }})
        </h3>

        <RecipeNutritionTable
          direction="vertical"
          :nutrition="recipe.nutrition"
        />
      </Card>

      <GoogleAd
        :title="
          useScreensize().screenIsGreaterThanOrEqualTo('lg')
            ? 'Sponsored'
            : undefined
        "
        code="2137793897"
      />
    </div>

    <div class="flex flex-col space-y-3">
      <Card class="space-y-3">
        <SubHeading classes="text-primary-dark">Method</SubHeading>

        <article
          class="prose prose-lg max-w-none md:prose-xl"
          v-html="recipe.method"
        />

        <h3 class="mt-4 mb-2 text-base font-semibold lg:hidden">
          Nutritional Information (Per {{ recipe.nutrition.portion_size }})
        </h3>

        <RecipeNutritionTable
          class="lg:hidden"
          :nutrition="recipe.nutrition"
        />
      </Card>

      <Comments
        :id="recipe.id"
        :comments="allComments"
        module="recipe"
        @load-more="loadMoreComments"
      />
    </div>
  </div>
</template>
