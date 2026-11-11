<script setup lang="ts">
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import StarRating from '@/Components/StarRating.vue';
import { ShopProductIndex } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';
import { computed, useTemplateRef } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon, ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useAddToBasket from '@/composables/useAddToBasket';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = withDefaults(
  defineProps<{ product: ShopProductIndex; eager?: boolean }>(),
  { eager: false },
);

const ratingText = computed((): string | null => {
  if (!props.product.rating) {
    return null;
  }

  const label = pluralise('rating', props.product.rating.count);

  return `${props.product.rating.count} ${label}`;
});

const { addBasketForm, prepareAddBasketForm, submitAddBasketForm } =
  useAddToBasket();

prepareAddBasketForm(props.product.id, props.product.primary_variant);

const addToBasket = () => {
  useJourneyTracking().logEvent('clicked', 'ShopProductCard/AddToBasket', {
    title: props.product.title,
    id: props.product.id,
  });

  submitAddBasketForm({
    only: ['basket'],
  });
};

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'ShopProductCard',
  {
    title: props.product.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="overflow-hidden"
  >
    <div class="group flex flex-1 flex-col">
      <Link
        :href="product.link"
        class="relative -m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :src="product.image"
          :alt="product.title"
          :loading="eager ? 'eager' : 'lazy'"
          :fetchpriority="eager ? 'high' : 'auto'"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover"
        />

        <div
          v-if="!product.in_stock"
          class="absolute top-0 right-0 p-3"
        >
          <p
            class="rounded-sm bg-red-dark/90 px-3 py-2 font-semibold text-white shadow-lg"
          >
            Out of stock
          </p>
        </div>
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link
          :href="product.link"
          prefetch
        >
          <h2
            class="line-clamp-2 text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="product.title"
          />
        </Link>

        <div class="flex">
          <p
            class="prose line-clamp-3 max-w-none md:prose-lg"
            v-html="product.description"
          />
        </div>

        <div class="flex items-center justify-between gap-3">
          <p
            class="text-2xl font-semibold md:max-xl:text-3xl xl:text-4xl"
            v-text="product.price"
          />

          <div
            v-if="product.rating && product.rating.count > 0"
            class="flex flex-col items-end space-y-1"
          >
            <StarRating
              :rating="product.rating.average"
              size="w-4 h-4 xl:w-5 xl:h-5"
              show-all
            />

            <span
              class="text-right text-sm font-semibold xl:text-base"
              v-text="ratingText"
            />
          </div>
        </div>

        <p
          v-if="product.footnote"
          class="mt-auto text-sm font-semibold text-grey-darker"
          v-text="product.footnote"
        />
      </div>
    </div>

    <div class="-mx-4 mt-4 -mb-4 bg-primary-lightest/60 p-4">
      <div
        class="grid gap-3"
        :class="
          product.number_of_variants === 1
            ? 'grid-cols-1 xmd:grid-cols-2'
            : 'grid-cols-1'
        "
      >
        <CoeliacButton
          :as="Link"
          label="Find out more"
          classes="w-full justify-center text-center"
          theme="light"
          size="xl"
          :href="product.link"
          :icon="ArrowRightIcon"
          icon-position="right"
          bold
        />

        <CoeliacButton
          v-if="product.number_of_variants === 1"
          as="button"
          type="button"
          label="Add to Basket"
          classes="w-full justify-center text-center"
          theme="secondary"
          :icon="ShoppingBagIcon"
          icon-position="right"
          size="xl"
          bold
          :loading="addBasketForm.processing"
          :disabled="!product.in_stock"
          @click="product.in_stock ? addToBasket() : undefined"
        />
      </div>
    </div>
  </Card>
</template>
