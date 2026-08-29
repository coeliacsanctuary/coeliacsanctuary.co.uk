<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShopCategoryIndex } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = defineProps<{
  categories: ShopCategoryIndex[];
}>();

type Panel = {
  slug: string;
  summary: string;
  rows: { label: string; value: string }[];
};

const panels: Panel[] = [
  {
    slug: 'standard-coeliac-travel-cards',
    summary:
      'For anyone who is coeliac or gluten free with no other allergies to explain.',
    rows: [
      { label: 'Languages per card', value: 'Two, one on each side' },
      { label: 'Available in', value: 'Over 50 languages' },
      { label: 'Best for', value: 'Coeliac and gluten free only' },
      { label: 'The reverse', value: 'Your second language' },
    ],
  },
  {
    slug: 'coeliac-plus-other-allergen-cards',
    summary:
      'For anyone who is gluten free and needs to flag other allergens or dietary needs too.',
    rows: [
      { label: 'Languages per card', value: 'One' },
      { label: 'Available in', value: 'My 10 most popular languages' },
      { label: 'Best for', value: 'Gluten free plus other allergens' },
      { label: 'The reverse', value: 'Tick box list of other allergens' },
    ],
  },
];

const cards = computed(() =>
  panels
    .map((panel) => ({
      ...panel,
      category: props.categories.find((category) =>
        category.link.endsWith(panel.slug),
      ),
    }))
    .filter((panel) => !!panel.category),
);
</script>

<template>
  <Card
    v-if="cards.length"
    class="space-y-4"
  >
    <SubHeading
      as="h2"
      text-size="small"
    >
      Which card do I need?
    </SubHeading>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <div class="grid gap-4 lg:grid-cols-2">
      <div
        v-for="card in cards"
        :key="card.slug"
        class="flex flex-col overflow-hidden rounded-sm border border-primary-light/60 bg-primary-lightest/40"
      >
        <Link
          :href="card.category!.link"
          prefetch
        >
          <img
            :src="card.category!.image"
            :alt="card.category!.title"
            width="1200"
            height="630"
            loading="lazy"
            class="aspect-[1200/630] w-full object-cover object-center"
          />
        </Link>

        <div class="flex flex-1 flex-col space-y-3 p-4">
          <div class="flex flex-wrap items-baseline justify-between gap-x-3">
            <h3 class="font-coeliac text-xl font-semibold text-primary-dark">
              {{ card.category!.title }}
            </h3>

            <p
              v-if="card.category!.price"
              class="text-sm font-semibold text-grey-dark"
              v-text="card.category!.price"
            />
          </div>

          <p
            class="prose max-w-none"
            v-text="card.summary"
          />

          <dl class="flex flex-1 flex-col divide-y divide-primary-light/60">
            <div
              v-for="row in card.rows"
              :key="row.label"
              class="flex flex-wrap justify-between gap-x-4 py-2 text-sm"
            >
              <dt
                class="font-semibold text-grey-darker"
                v-text="row.label"
              />

              <dd
                class="text-right text-grey-dark"
                v-text="row.value"
              />
            </div>
          </dl>

          <CoeliacButton
            :as="Link"
            :href="card.category!.link"
            :label="`Browse all ${card.category!.products_count} cards`"
            classes="w-full justify-center text-center"
            theme="light"
            size="lg"
            :icon="ArrowRightIcon"
            icon-position="right"
            bold
          />
        </div>
      </div>
    </div>
  </Card>
</template>
