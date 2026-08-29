<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import SnapScrollRow from '@/Components/SnapScrollRow.vue';
import StarRating from '@/Components/StarRating.vue';
import { ShopIndexReview } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{ reviews: ShopIndexReview[] }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'ShopIndexCustomerReviews',
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4"
  >
    <SubHeading
      as="h2"
      text-size="small"
    >
      What my customers say
    </SubHeading>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <p class="prose max-w-none md:prose-lg">
      Every review below comes from someone who actually placed an order — I
      only ask for feedback once a parcel has been sent.
    </p>

    <SnapScrollRow
      :items="reviews"
      grid-classes="sm:grid-cols-2 xl:grid-cols-3"
      :label="(review) => `Review from ${review.name}`"
    >
      <template #default="{ item, itemClasses }">
        <div
          :class="itemClasses"
          class="flex h-full flex-col space-y-2 border-l-8 border-secondary bg-primary-light/30 p-4"
        >
          <div class="flex flex-wrap items-center justify-between gap-2">
            <span
              class="font-semibold"
              v-text="item.name"
            />

            <StarRating
              :rating="item.rating"
              size="w-4 h-4"
              show-all
            />
          </div>

          <p
            class="prose max-w-none flex-1"
            v-text="item.review"
          />

          <Link
            v-if="item.product"
            :href="item.product.link"
            class="text-sm font-semibold text-primary-dark transition hover:text-black"
            prefetch
          >
            {{ item.product.title }}
          </Link>
        </div>
      </template>
    </SnapScrollRow>
  </Card>
</template>
