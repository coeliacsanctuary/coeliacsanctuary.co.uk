<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShopCategoryIndex, TravelCardDigitalType } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = defineProps<{
  categories: ShopCategoryIndex[];
  digitalCardTypes: TravelCardDigitalType[];
  digitalCardsEnabled: boolean;
}>();

type Panel = {
  slug: string;
  summary: string;
  rows: (products: number) => { label: string; value: string }[];
};

const panels: Panel[] = [
  {
    slug: 'standard-coeliac-travel-cards',
    summary:
      'For anyone who is coeliac or gluten free with no other allergies to explain.',
    rows: (products: number) => [
      { label: 'Languages per card', value: 'Two, one on each side' },
      { label: 'Available in', value: `${(products - 1) * 2} languages` },
      { label: 'Best for', value: 'Coeliac and gluten free only' },
      { label: 'The reverse', value: 'Your second language' },
    ],
  },
  {
    slug: 'coeliac-plus-other-allergen-cards',
    summary:
      'For anyone who is gluten free and needs to flag other allergens or dietary needs too.',
    rows: (products: number) => [
      { label: 'Languages per card', value: 'One' },
      {
        label: 'Available in',
        value: `My ${products} most popular languages`,
      },
      { label: 'Best for', value: 'Gluten free plus other allergens' },
      { label: 'The reverse', value: 'Tick box list of other allergens' },
    ],
  },
  props.digitalCardsEnabled
    ? {
        slug: 'digital-coeliac-gluten-free-travel-cards',
        summary:
          'For anyone who needs a card today, or would rather carry it on their phone than in their bag.',
        rows: (products: number) => [
          { label: 'Languages per card', value: 'One' },
          { label: 'Available in', value: `${products} languages` },
          {
            label: 'Best for',
            value: 'Coeliac and gluten free, when you need it today',
          },
          {
            label: 'On your phone',
            value: 'Any browser, or Apple and Google Wallet',
          },
        ],
      }
    : undefined,
].filter((panel) => panel !== undefined);

type PanelCategory = {
  title: string;
  link: string;
  image: string;
  price?: string | null;
  products_count?: number;
};

const allCategories = computed((): PanelCategory[] => [
  ...props.categories,
  ...props.digitalCardTypes.map((type) => ({
    title: type.title,
    link: type.link,
    image: type.image,
    products_count: type.products_count,
    price: type.price,
  })),
]);

const cards = computed(() =>
  panels
    .map((panel) => ({
      ...panel,
      category: allCategories.value.find((category) =>
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

    <div
      class="grid gap-4"
      :class="digitalCardsEnabled ? 'lg:grid-cols-3' : 'lg:grid-cols-2'"
    >
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
              v-for="row in card.rows(card.category!.products_count ?? 0)"
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
