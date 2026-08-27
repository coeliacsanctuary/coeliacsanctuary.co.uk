<script lang="ts" setup>
import HomeHero from '@/Components/PageSpecific/Home/HomeHero.vue';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import HomeContentRow from '@/Components/PageSpecific/Home/HomeContentRow.vue';
import { HomeHoverItem } from '@/types/Types';
import HomeCollection from '@/Components/PageSpecific/Home/HomeCollection.vue';
import { HomepageCollection } from '@/types/CollectionTypes';
import {
  EaterySimpleHomeResource,
  EaterySimpleReviewResource,
} from '@/types/EateryTypes';
import HomeLatestEateries from '@/Components/PageSpecific/Home/HomeLatestEateries.vue';
import HomeLatestReviews from '@/Components/PageSpecific/Home/HomeLatestReviews.vue';
import HomeNewsletterSignup from '@/Components/PageSpecific/Home/HomeNewsletterSignup.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{
  blogs: { latest: HomeHoverItem[]; top: HomeHoverItem[] };
  recipes: { latest: HomeHoverItem[]; top: HomeHoverItem[] };
  collections: HomepageCollection[];
  latestReviews: EaterySimpleReviewResource[];
  latestEateries: EaterySimpleHomeResource[];
}>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('about-card'),
  'scrolled_into_view',
  'Home/AboutCard',
);
</script>

<template>
  <HomeHero />

  <div class="flex flex-col space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4">
    <div class="flex w-full flex-col space-y-4 lg:w-3/4">
      <Card class="space-y-4 md:p-5">
        <Heading as="h1">
          Coeliac Sanctuary - Gluten Free Blog by Alison Peters
        </Heading>

        <div class="prose max-w-none sm:prose-lg">
          <p>
            Welcome to Coeliac Sanctuary, my sanctuary for Coeliac disease. I
            share a wealth of resources, tips, and tricks tailored specifically
            for people with Coeliac disease. Explore my extensive collection of
            blogs and recipes for delicious gluten free options. Whether you're
            dining out locally or traveling abroad, my comprehensive eating out
            guide and Coeliac travel cards, available in my shop, ensure a safe
            and worry-free experience. Join my community and embrace a
            gluten-free lifestyle with confidence.
          </p>
        </div>
      </Card>

      <template v-if="collections.length">
        <HomeCollection
          v-for="collection in collections"
          :key="collection.title"
          :collection="collection"
        />
      </template>

      <HomeNewsletterSignup />

      <HomeContentRow
        :items="blogs.top"
        title="Top Blogs"
        view-all-href="/blog"
        view-all-label="All blogs"
      >
        <p class="prose prose-lg mb-2 max-w-none font-semibold lg:prose-xl">
          Explore some of the most popular articles from the Coeliac Sanctuary
          blog.
        </p>
      </HomeContentRow>

      <HomeContentRow
        :items="blogs.latest"
        title="Latest Blogs"
        view-all-href="/blog"
        view-all-label="All blogs"
      />

      <HomeContentRow
        :items="recipes.top"
        :per-row="4"
        title="Top Recipes"
        view-all-href="/recipe"
        view-all-label="All recipes"
      >
        <p class="prose prose-lg mb-2 max-w-none font-semibold lg:prose-xl">
          See what everyone is making today, and check out the most popular
          recipes on Coeliac Sanctuary.
        </p>
      </HomeContentRow>

      <HomeContentRow
        :items="recipes.latest"
        :per-row="4"
        title="Latest Recipes"
        view-all-href="/recipe"
        view-all-label="All recipes"
      />
    </div>

    <aside class="flex w-full flex-col space-y-4 lg:w-1/4">
      <Card
        ref="about-card"
        class="space-y-4 overflow-hidden"
        faded
        theme="primary-light"
      >
        <img
          alt="Alison Peters - Coeliac Sanctuary"
          src="/images/misc/alison-cake.webp"
          class="-mx-4 -mt-4 w-[calc(100%+2rem)] max-w-none"
          width="600"
          height="600"
          loading="lazy"
        />

        <p class="prose max-w-none sm:prose-lg">
          <strong>Hey, I'm Alison</strong>, I was diagnosed with Coeliac in
          2014, Coeliac Sanctuary blossomed from the tough time I was going
          through during diagnosis and almost 12 months of tests as a way to
          share recipes, information and keep track of places to eat safely,
          with a shop added later selling translation cards, wristbands and
          more.
        </p>

        <Link
          class="-mx-4 mt-4! -mb-4 flex items-center justify-center bg-white/40 p-4 font-semibold transition hover:bg-white/70"
          href="/about"
        >
          Read more about me
        </Link>
      </Card>

      <HomeLatestReviews :reviews="latestReviews" />

      <HomeLatestEateries :eateries="latestEateries" />
    </aside>
  </div>
</template>
