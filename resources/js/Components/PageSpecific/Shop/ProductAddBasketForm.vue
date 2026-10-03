<script setup lang="ts">
import CoeliacButton from '@/Components/CoeliacButton.vue';
import {
  ShopProductDetail,
  ShopProductVariant,
  ShopTravelCardProductDetail,
} from '@/types/Shop';
import { computed, onMounted, ref, Ref, watch } from 'vue';
import useAddToBasket from '@/composables/useAddToBasket';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useScreensize from '@/composables/useScreensize';
import ProductQuantitySwitcher from '@/Components/PageSpecific/Shop/ProductQuantitySwitcher.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import ProductSelectVariant from '@/Components/PageSpecific/Shop/ProductSelectVariant.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import { Link } from '@inertiajs/vue3';
import { watchDeep } from '@vueuse/core';
import digitalCards from '@/Pages/Shop/DigitalCards.vue';

type Product = ShopProductDetail | ShopTravelCardProductDetail;

const props = defineProps<{ product: Product }>();

const selectedVariant: Ref<ShopProductVariant | undefined> = ref();
const quantity: Ref<number> = ref(1);
const isInStock: Ref<boolean> = ref(true);

const emit = defineEmits(['selected-variant']);

const isTravelCardProduct = (
  product: Product,
): product is ShopTravelCardProductDetail =>
  (product as ShopTravelCardProductDetail).is_travel_card;

const travelCardProduct = computed(() =>
  isTravelCardProduct(props.product) ? props.product : undefined,
);

const checkStock = () => {
  if (!selectedVariant.value) {
    isInStock.value = false;
    return;
  }

  isInStock.value = selectedVariant.value.quantity > 0;
};

const availableQuantity = computed(() => selectedVariant.value?.quantity);

const addDigitalCards = ref<{ [T: string]: boolean }>({});

onMounted(() => {
  if (travelCardProduct.value?.digital_cards.length) {
    travelCardProduct.value.digital_cards.forEach((card) => {
      addDigitalCards.value[card.slug] = false;
    });
  }
});

const {
  addBasketForm,
  prepareAddBasketForm,
  withMultipleDigitalCards,
  submitAddBasketForm,
} = useAddToBasket();

const runningTotal = computed((): number => {
  let total = props.product.prices.raw_price * quantity.value;

  if (travelCardProduct.value?.digital_cards.length) {
    travelCardProduct.value.digital_cards.forEach((card) => {
      if (addDigitalCards.value[card.slug]) {
        total += card.raw_price;
      }
    });
  }

  return total / 100;
});

const buttonLabel = computed((): string => {
  let label = 'Add To Basket';

  if (runningTotal.value > 0) {
    label += ` - £${runningTotal.value.toFixed(2)}`;
  }

  return label;
});

onMounted(() => {
  if (props.product.variants.length === 1) {
    // eslint-disable-next-line prefer-destructuring
    selectedVariant.value = props.product.variants[0];
    prepareAddBasketForm(props.product.id, selectedVariant.value.id);
  }

  checkStock();
});

watch(selectedVariant, () => {
  emit('selected-variant', selectedVariant.value);
  checkStock();
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
  );

  quantity.value = 1;

  useJourneyTracking().logEvent(
    'clicked',
    'ShopProduct/AddBasketForm/ChangedVariant',
    {
      title: props.product.title,
      variant: selectedVariant.value?.title,
    },
  );
});

watch(quantity, () => {
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
    quantity.value,
  );

  useJourneyTracking().logEvent(
    'clicked',
    'ShopProduct/AddBasketForm/ChangedQuantity',
    {
      title: props.product.title,
      quantity: quantity.value,
    },
  );
});

const addToBasket = () => {
  if (travelCardProduct.value?.digital_cards.length) {
    const digitalCardsToAdd: number[] = [];

    travelCardProduct.value.digital_cards.forEach((card) => {
      if (addDigitalCards.value[card.slug]) {
        digitalCardsToAdd.push(card.id);
      }
    });

    if (digitalCardsToAdd.length) {
      withMultipleDigitalCards(digitalCardsToAdd);
    }
  }

  submitAddBasketForm({
    only: ['basket', 'errors'],
  });

  useJourneyTracking().logEvent('clicked', 'ShopProduct/AddBasketForm/Submit', {
    title: props.product.title,
    variant: selectedVariant.value?.title,
    quantity: quantity.value,
  });
};

const { screenIsGreaterThanOrEqualTo } = useScreensize();
</script>

<template>
  <div
    class="mt-3 w-full md:col-start-1 md:row-start-2 md:max-w-lg md:self-start"
  >
    <form
      class="flex w-full flex-col space-y-3"
      @submit.prevent="addToBasket()"
    >
      <ProductSelectVariant
        v-model="selectedVariant"
        :variants="product.variants"
        :variant-label="product.variant_title"
      />

      <div class="w-full *:w-full sm:flex sm:justify-between">
        <ProductQuantitySwitcher
          v-model.number="quantity"
          label="Quantity"
          name="quantity"
          :min="1"
          :disabled="!isInStock"
          :max="
            availableQuantity && availableQuantity <= 5
              ? availableQuantity
              : undefined
          "
          :error="addBasketForm.errors.quantity"
        />
      </div>

      <div
        v-if="availableQuantity === 0"
        class="font-semibold text-red-dark"
      >
        Sorry, this product is out of stock.
      </div>

      <p
        v-if="availableQuantity && availableQuantity <= 5"
        class="text-red"
      >
        Order soon, only {{ availableQuantity }} available
        {{
          product.variants.length > 0
            ? `in this ${product.variant_title.toLowerCase()}`
            : ''
        }}!
      </p>

      <div
        v-if="availableQuantity && travelCardProduct?.digital_cards.length"
        class="mt-4 flex flex-col space-y-2"
      >
        <h3 class="text-xl font-semibold">Digital Card Add Ons</h3>

        <p class="prose-sm max-w-none">
          Complete your {{ product.category.title }} by including my digital
          cards that can be accessed anywhere, on any device, at any time!
        </p>

        <p class="prose max-w-none">
          <Link
            :href="travelCardProduct.digital_card_link"
            target="_blank"
          >
            See more Digital Travel Cards
          </Link>
        </p>

        <ul class="flex flex-col space-y-2">
          <li
            v-for="card in travelCardProduct.digital_cards"
            :key="card.id"
            class="flex items-start space-x-2"
          >
            <FormCheckbox
              v-model="addDigitalCards[card.slug]"
              layout="left"
              xl
              :name="card.slug"
              :label="card.name"
              class="p-0!"
            />
            <span
              class="flex-no-shrink min-w-[50px] flex-1 flex-shrink-0 text-right font-semibold"
              v-text="`+${card.price}`"
            />
          </li>
        </ul>
      </div>

      <div class="flex items-center justify-center md:justify-between">
        <CoeliacButton
          as="button"
          :label="buttonLabel"
          :disabled="!isInStock"
          :theme="isInStock ? 'secondary' : 'negative'"
          :icon="
            screenIsGreaterThanOrEqualTo('xxs') ? ShoppingBagIcon : undefined
          "
          icon-position="right"
          size="xxl"
          :loading="addBasketForm.processing"
        />
      </div>

      <div
        v-if="!availableQuantity && travelCardProduct?.digital_cards.length"
        class="mt-4 flex flex-col space-y-2"
      >
        <h3 class="text-xl font-semibold">Digital Travel Cards</h3>

        <p class="prose-sm max-w-none">
          Why not look at my digital cards that can be accessed anywhere, on any
          device, at any time, while this product is out of stock?
        </p>

        <CoeliacButton
          as="a"
          :href="travelCardProduct.digital_card_link"
          target="_blank"
          bold
          classes="justify-center text-center"
          label="See more Digital Travel Cards"
        />
      </div>
    </form>
  </div>
</template>
