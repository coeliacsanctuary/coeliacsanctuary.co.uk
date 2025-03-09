<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Heading from '@/Components/Heading.vue';
import Card from '@/Components/Card.vue';
</script>

<template>
  <div class="grid lg:grid-cols-2 gap-4">
    <Card class="flex flex-col space-y-4">
      <Heading>Coeliac Travel Cards</Heading>

      <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
        Our standard coeliac gluten free travel and translation cards offer two
        languages for the price of one — one on each side — and are available in
        over 50 languages!
      </p>

      <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl flex-1">
        Designed specifically for those with coeliac disease and no additional
        allergies, these cards are professionally translated to clearly explain
        coeliac disease, a gluten free diet, safe and unsafe foods,
        cross-contamination risks, and key points to watch out for.
      </p>

      <CoeliacButton
        :as="Link"
        href="/shop/standard-coeliac-travel-cards"
        size="xl"
        label="View all Coeliac Travel Cards"
        classes="items-center justify-center text-2xl! font-semibold lg:text-3xl!"
      />
    </Card>

    <Card class="flex flex-col space-y-4">
      <Heading>Coeliac+ Travel Cards</Heading>

      <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
        Our Coeliac+ travel and translation cards are designed for those who are
        gluten free and also have additional dietary needs. Unlike our standard
        cards, each Coeliac+ card is just one language and comes in our 10 most
        popular languages.
      </p>

      <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl flex-1">
        On one side, you'll find a professionally translated explanation of
        coeliac disease, including cross-contamination risks. The reverse side
        features a series of checkboxes, allowing you to easily indicate any
        additional allergens, dietary restrictions, or preferences like
        vegetarian or vegan.
      </p>

      <CoeliacButton
        :as="Link"
        href="/shop/coeliac-cards"
        size="xl"
        label="View all Coeliac+ Travel Cards"
        classes="items-center justify-center text-2xl! font-semibold lg:text-3xl!"
      />
    </Card>
  </div>
</template>
