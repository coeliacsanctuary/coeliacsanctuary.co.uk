<script setup lang="ts">
import {
  RadioGroup,
  RadioGroupDescription,
  RadioGroupLabel,
  RadioGroupOption,
} from '@headlessui/vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Icon from '@/Components/Icon.vue';
import { ShopProductDetail, ShopProductVariant } from '@/types/Shop';
import { computed, nextTick, onMounted, ref, Ref, watch } from 'vue';
import useAddToBasket from '@/composables/useAddToBasket';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useScreensize from '@/composables/useScreensize';
import ProductQuantitySwitcher from '@/Components/PageSpecific/Shop/ProductQuantitySwitcher.vue';
import ProductSelectVariant from '@/Components/PageSpecific/Shop/ProductSelectVariant.vue';

const props = defineProps<{
  product: ShopProductDetail;
  defaultVariant?: ShopProductVariant;
}>();

const selectedVariant: Ref<ShopProductVariant | undefined> = ref();
const quantity: Ref<number> = ref(1);
const isInStock: Ref<boolean> = ref(true);

const emit = defineEmits(['selected-variant']);

const checkStock = () => {
  if (!selectedVariant.value) {
    isInStock.value = false;
    return;
  }

  isInStock.value = selectedVariant.value.quantity > 0;
};

const availableQuantity = computed(() => selectedVariant.value?.quantity);

const { addBasketForm, prepareAddBasketForm, submitAddBasketForm } =
  useAddToBasket();

const selectDefaultVariant = () => {
  if (props.product.variants.length === 1) {
    // eslint-disable-next-line prefer-destructuring
    selectedVariant.value = props.product.variants[0];
    prepareAddBasketForm(props.product.id, selectedVariant.value.id);

    return;
  }

  if (props.defaultVariant) {
    selectedVariant.value = props.defaultVariant;
    prepareAddBasketForm(props.product.id, selectedVariant.value.id);
  }
};

onMounted(() => {
  nextTick(() => {
    selectDefaultVariant();
    checkStock();
  });
});

watch(selectedVariant, () => {
  emit('selected-variant', selectedVariant.value);
  checkStock();
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
  );

  quantity.value = 1;
});

watch(quantity, () => {
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
    quantity.value,
  );
});

const addToBasket = () => {
  submitAddBasketForm({
    only: ['basket', 'errors'],
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
        :has-multiple-prices="product.has_multiple_prices"
      />

      <div
        v-if="selectedVariant?.variant_type !== 'digital'"
        class="w-full *:w-full sm:flex sm:justify-between"
      >
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
        v-else
        class="font-semibold"
      >
        Please note, this product is a digital download only.
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

      <div class="flex items-center justify-center md:justify-between">
        <CoeliacButton
          as="button"
          label="Add To Basket"
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
    </form>
  </div>
</template>
