<script lang="ts" setup>
import { PaginatedResponse } from '@/types/GenericTypes';
import { ref, Ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { RecipePage } from '@/types/RecipeTypes';
import Card from '@/Components/Card.vue';
import Comments from '@/Components/PageSpecific/Shared/Comments.vue';
import RecipeNutritionTable from '@/Components/PageSpecific/Recipes/RecipeNutritionTable.vue';
import { Page } from '@inertiajs/core';
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import SubHeading from '@/Components/SubHeading.vue';
import FeaturedInCollectionCard from '@/Components/PageSpecific/Shared/FeaturedInCollectionCard.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import RenderedString from '@/Components/RenderedString.vue';
import RecipeHeader from '@/Components/PageSpecific/Recipes/RecipeHeader.vue';
import RecipeAttributes from '@/Components/PageSpecific/Recipes/RecipeAttributes.vue';
import RecipeIngredients from '@/Components/PageSpecific/Recipes/RecipeIngredients.vue';
import RecipeMethod from '@/Components/PageSpecific/Recipes/RecipeMethod.vue';
import RelatedRecipesRow from '@/Components/PageSpecific/Recipes/RelatedRecipesRow.vue';

const props = defineProps<{
  recipe: RecipePage;
  comments: PaginatedResponse<Comment>;
  backLink: string;
}>();

const header = ref<HTMLElement>();

const headerEnd = ref<HTMLElement>();

const recipeElem = ref<HTMLElement>();

const allComments: Ref<PaginatedResponse<Comment>> = ref(props.comments);
const isLoadingComments = ref(false);
const hasLoadedMoreComments = ref(false);

const loadMoreComments = () => {
  if (!props.comments.links.next) {
    return;
  }

  isLoadingComments.value = true;

  router.get(
    props.comments.links.next,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      only: ['comments'],
      preserveUrl: true,
      onSuccess: (page: Page<{ comments?: PaginatedResponse<Comment> }>) => {
        isLoadingComments.value = false;
        hasLoadedMoreComments.value = true;

        if (page.props.comments) {
          allComments.value.data.push(...page.props.comments.data);
          allComments.value.links = page.props.comments.links;
          allComments.value.meta = page.props.comments.meta;
        }
      },
    },
  );
};

const handleCommentReset = () => {
  header.value?.scrollIntoView({ behavior: 'smooth' });

  router.get(
    props.comments.links.first,
    {},
    {
      preserveState: true,
      only: ['comments'],
      preserveUrl: true,
      onSuccess: (page: Page<{ comments?: PaginatedResponse<Comment> }>) => {
        hasLoadedMoreComments.value = false;

        if (page.props.comments) {
          allComments.value.data = page.props.comments.data;
          allComments.value.links = page.props.comments.links;
          allComments.value.meta = page.props.comments.meta;
        }
      },
    },
  );
};
</script>

<template>
  <div
    ref="header"
    class="absolute"
  />

  <RecipeHeader
    :recipe="recipe"
    :back-link="backLink"
    :recipe-element="recipeElem"
  />

  <div
    ref="headerEnd"
    class="absolute"
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <div
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="recipe.description"
    />

    <RecipeAttributes
      :features="recipe.features"
      :allergens="recipe.allergens"
    />
  </Card>

  <Card v-if="recipe.body">
    <div class="prose prose-lg max-w-none md:prose-xl">
      <RenderedString :content="recipe.body" />
    </div>
  </Card>

  <FaqCard
    v-if="recipe.faqs"
    :faqs="recipe.faqs"
    :title="`Here are some tips and FAQs about ${recipe.short_title || recipe.title}`"
  />

  <div
    class="relative flex flex-col space-y-3 lg:flex-row lg:space-y-0 lg:space-x-3"
  >
    <aside
      class="flex flex-col space-y-3 lg:w-[350px] lg:flex-shrink-0 lg:self-start"
    >
      <Card>
        <div
          ref="recipeElem"
          class="absolute"
        />

        <RecipeIngredients :groups="recipe.ingredients" />
      </Card>

      <FeaturedInCollectionCard
        v-if="recipe.featured_in?.length"
        :collections="recipe.featured_in"
        title="This recipe is featured in"
      />
    </aside>

    <div class="flex flex-1 flex-col space-y-3">
      <Card class="space-y-4">
        <SubHeading classes="text-primary-dark">Method</SubHeading>

        <RecipeMethod :method="recipe.method" />

        <div class="space-y-2">
          <h3 class="text-base font-semibold">
            Nutritional Information (Per {{ recipe.nutrition.portion_size }})
          </h3>

          <RecipeNutritionTable :nutrition="recipe.nutrition" />
        </div>

        <p
          v-if="recipe.updated"
          class="text-sm text-grey-dark"
          v-text="`Last updated ${recipe.updated}`"
        />
      </Card>

      <Card
        v-if="['Alison Peters', 'Alison Wheatley'].includes(recipe.author)"
        faded
        theme="primary-light"
      >
        <div
          class="justify-center md:flex md:flex-row md:space-x-2 md:space-x-4"
        >
          <img
            alt="Alison Peters"
            class="float-left mr-2 mb-2 w-1/4 max-w-[150px] rounded-full"
            src="/images/misc/alison.png"
          />
          <div class="prose max-w-2xl md:prose-xl">
            <strong>Alison Peters</strong> has been Coeliac since June 2014 and
            launched Coeliac Sanctuary in August of that year, and since then
            has aimed to provide a one stop shop for Coeliacs, from blogs, to
            recipes, eating out guide and online shop.
          </div>
        </div>
      </Card>

      <Card
        v-if="recipe.author === 'Jamie Peters'"
        faded
        theme="primary-light"
      >
        <div
          class="justify-center md:flex md:flex-row md:space-x-2 md:space-x-4"
        >
          <img
            alt="Alison Peters"
            class="float-left mr-2 mb-2 w-1/4 max-w-[150px] rounded-full"
            src="/images/misc/jamie.png"
          />
          <div class="prose max-w-2xl md:prose-xl">
            While not a coeliac, <strong>Jamie Peters</strong> is married to
            one, he's the brains behind this website, and alongside software
            development he has always enjoyed cooking and baking, and will adapt
            his old family favourites so Alison can eat them too.
          </div>
        </div>
      </Card>
    </div>
  </div>

  <RelatedRecipesRow
    v-if="recipe.related_recipes?.length"
    :recipes="recipe.related_recipes"
    class="mt-3"
  />

  <Comments
    :id="recipe.id"
    :comments="allComments"
    module="recipe"
    class="mt-3"
    :is-loading="isLoadingComments"
    :has-loaded-more="hasLoadedMoreComments"
    @load-more="loadMoreComments"
    @reset="handleCommentReset"
  />

  <JumpToContentButton
    v-if="recipeElem && headerEnd"
    :anchor="recipeElem"
    :show-after="headerEnd"
    label="Jump to recipe"
  />
</template>
