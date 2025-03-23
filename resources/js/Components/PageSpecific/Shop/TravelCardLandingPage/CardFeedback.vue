<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { TravelCardFeedbackItem } from '@/types/Shop';

defineProps<{
  feedback: TravelCardFeedbackItem[];
}>();
</script>

<template>
  <Card class="flex flex-col space-y-4">
    <Heading>Customer Reviews</Heading>

    <p
      class="prose prose-lg max-w-none md:max-xl:prose-xl xl:prose-2xl font-semibold"
    >
      Thinking of purchasing some of our products, take a look at what some of
      our previous customers have said about them!
    </p>

    <div
      v-for="item in feedback"
      :key="item.name"
      class="border-l-8 border-secondary bg-primary-light/30 p-4"
    >
      <div class="flex items-center justify-between">
        <div class="flex flex-col">
          <h4 class="font-bold lg:text-xl">
            <span v-text="item.name"></span>
            -
            <Link
              class="text-primary-dark hover:underline"
              :href="item.link"
              >{{ item.product }}</Link
            >
          </h4>
        </div>
      </div>

      <div
        class="prose mt-2 max-w-none lg:prose-lg"
        v-html="item.review"
      />
    </div>
  </Card>
</template>
