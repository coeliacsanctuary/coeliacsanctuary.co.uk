<script lang="ts" setup>
import HomeHero from '@/Components/PageSpecific/Home/HomeHero.vue';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import HomeHoverGroup from '@/Components/PageSpecific/Home/HomeHoverGroup.vue';
import { HomeHoverItem } from '@/types/Types';
import HomeCollection from '@/Components/PageSpecific/Home/HomeCollection.vue';
import { HomepageCollection } from '@/types/CollectionTypes';
import {
  EaterySimpleHomeResource,
  EaterySimpleReviewResource,
} from '@/types/EateryTypes';
import HomeLatestEateries from '@/Components/PageSpecific/Home/HomeLatestEateries.vue';
import HomeLatestReviews from '@/Components/PageSpecific/Home/HomeLatestReviews.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import HomeNewsletterSignup from '@/Components/PageSpecific/Home/HomeNewsletterSignup.vue';

defineProps<{
  blogs: HomeHoverItem[];
  recipes: HomeHoverItem[];
  collections: HomepageCollection[];
  latestReviews: EaterySimpleReviewResource[];
  latestEateries: EaterySimpleHomeResource[];
}>();
</script>

<template>
  <HomeHero />

  <div class="flex flex-col space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4">
    <div class="flex w-full flex-col space-y-4 lg:w-3/4">
      <Card class="md:p-5">
        <h1 class="mb-3 font-coeliac text-3xl font-semibold md:text-[2.9rem]">
          Coeliac Sanctuary - Gluten Free Blog by Alison Peters
        </h1>

        <div class="prose max-w-none sm:prose-lg">
          <p>
            Welcome to Coeliac Sanctuary, your ultimate sanctuary for Coeliac
            disease. I provide a wealth of resources, tips, and tricks tailored
            specifically for people with Coeliac disease. Explore our extensive
            collection of blogs and recipes for delicious gluten free options.
            Whether you're dining out locally or traveling abroad, our
            comprehensive eating out guide and Coeliac travel cards, available
            in our shop, ensure a safe and worry-free experience. Join our
            community and embrace a gluten-free lifestyle with confidence.
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

      <HomeHoverGroup
        :items="blogs"
        title="Latest Blogs"
      />

      <GoogleAd
        :key="$page.url"
        code="9266309021"
      />

      <HomeHoverGroup
        :items="recipes"
        :per-row="4"
        title="Latest Recipes"
      />
    </div>

    <div class="flex w-full flex-col space-y-4 lg:w-1/4">
      <Card
        class="space-y-4"
        faded
        theme="primary-light"
      >
        <img
          alt="Alison Peters - Coeliac Sanctuary"
          src="/images/misc/alison-cake.png"
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

        <p>
          <Link
            class="font-semibold transition hover:text-primary-dark"
            href="/about"
          >
            Read more about me
          </Link>
        </p>
      </Card>

      <HomeLatestReviews :reviews="latestReviews" />

      <HomeLatestEateries :eateries="latestEateries" />
    </div>
  </div>
</template>
