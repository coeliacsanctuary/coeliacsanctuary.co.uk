<script setup lang="ts">
import { pluralise } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';
import { ShopProductIndex } from '@/types/Shop';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useScreensize from '@/composables/useScreensize';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useAddToBasket from '@/composables/useAddToBasket';

const props = defineProps<{
  product: ShopProductIndex;
}>();

const ratingText = computed((): string | null => {
  if (!props.product.rating) {
    return null;
  }

  const label = pluralise('rating', props.product.rating.count);

  return `${props.product.rating.count} ${label}`;
});

const { screenIsGreaterThanOrEqualTo } = useScreensize();

const { addBasketForm, prepareAddBasketForm, submitAddBasketForm } =
  useAddToBasket();

prepareAddBasketForm(props.product.id, props.product.primary_variant);

const addToBasket = () => {
  submitAddBasketForm({
    only: ['basket'],
  });
};
</script>

<template>
  <div
    class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white"
  >
    <Link
      class="bg-gray-200"
      :href="product.link"
      prefetch
    >
      <img
        :src="product.image"
        :alt="product.title"
      />
    </Link>
    <div class="flex flex-1 flex-col space-y-2 p-4">
      <h3
        class="font-semibold group-hover:text-primary-dark xxs:max-xmd:text-lg xmd:max-2xl:text-xl 2xl:text-2xl"
      >
        <Link :href="product.link">
          {{ product.title }}
        </Link>
      </h3>
      <p
        class="prose-sm text-gray-500 md:max-xl:prose-base xl:prose-lg"
        v-text="product.description"
      />

      <div class="flex flex-1 justify-between items-end">
        <p
          class="text-2xl font-semibold text-gray-900 md:max-xl:text-3xl xl:text-4xl"
        >
          {{ product.price }}
        </p>

        <div
          v-if="product.rating && product.rating.count > 0"
          class="flex flex-col space-y-1"
        >
          <StarRating
            :rating="product.rating.average"
            size="w-4 h-4 xl:w-5 xl:h-5"
          />
          <span
            class="text-right text-sm font-semibold xl:text-base"
            v-text="ratingText"
          />
        </div>
      </div>
    </div>

    <div
      class="mb-3 flex items-center gap-3 px-3 xmd:mb-4 xmd:px-4"
      :class="
        product.number_of_variants === 1 ? 'justify-between' : 'justify-center'
      "
    >
      <CoeliacButton
        :as="Link"
        label="Find out more"
        classes="text-center text-white"
        :size="screenIsGreaterThanOrEqualTo('xl') ? 'xxl' : 'lg'"
        :href="product.link"
        bold
      />

      <CoeliacButton
        v-if="product.number_of_variants === 1"
        as="button"
        type="button"
        :label="
          product.primary_variant_quantity > 0
            ? 'Add to Basket'
            : 'Out of stock'
        "
        classes="text-center"
        :theme="product.primary_variant_quantity > 0 ? 'secondary' : 'negative'"
        :icon="
          screenIsGreaterThanOrEqualTo('xxs') ? ShoppingBagIcon : undefined
        "
        icon-position="right"
        :size="screenIsGreaterThanOrEqualTo('xl') ? 'xxl' : 'lg'"
        bold
        :loading="addBasketForm.processing"
        :disabled="product.primary_variant_quantity === 0"
        @click="
          product.primary_variant_quantity > 0 ? addToBasket() : undefined
        "
      />
    </div>
  </div>
</template>
