<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import SnapScrollRow from '@/Components/SnapScrollRow.vue';
import ShopPopularProductTile from '@/Components/PageSpecific/Shop/ShopPopularProductTile.vue';
import { ShopPopularProduct } from '@/types/Shop';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{ products: ShopPopularProduct[] }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'ShopIndexPopularProducts',
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4"
  >
    <div class="flex flex-wrap items-center justify-between gap-2">
      <SubHeading
        as="h2"
        text-size="small"
      >
        Most popular right now
      </SubHeading>

      <Link
        href="/shop"
        class="inline-flex items-center space-x-2 font-semibold text-primary-dark transition hover:text-black"
        prefetch
      >
        <span>All categories</span>

        <ArrowRightIcon class="size-4" />
      </Link>
    </div>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <p class="prose max-w-none md:prose-lg">
      The products my customers have been ordering most this week.
    </p>

    <SnapScrollRow
      :items="products"
      grid-classes="sm:grid-cols-2 xl:grid-cols-3"
      :label="(product) => `Show ${product.title}`"
    >
      <template #default="{ item, itemClasses }">
        <ShopPopularProductTile
          :product="item"
          :class="itemClasses"
        />
      </template>
    </SnapScrollRow>
  </Card>
</template>
