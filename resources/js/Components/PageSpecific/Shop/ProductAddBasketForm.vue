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
import { computed, onMounted, ref, Ref, watch } from 'vue';
import useAddToBasket from '@/composables/useAddToBasket';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useScreensize from '@/composables/useScreensize';
import ProductQuantitySwitcher from '@/Components/PageSpecific/Shop/ProductQuantitySwitcher.vue';
import ProductSelectVariant from '@/Components/PageSpecific/Shop/ProductSelectVariant.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';

const props = defineProps<{ product: ShopProductDetail }>();

const selectedVariant: Ref<ShopProductVariant | undefined> = ref();
const quantity: Ref<number> = ref(1);
const isInStock: Ref<boolean> = ref(true);
const includeAddOn: Ref<boolean> = ref(false);

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

const runningTotal = computed((): number => {
  let total = props.product.prices.raw_price * quantity.value;

  if (includeAddOn.value && props.product.add_ons) {
    total += props.product.add_ons.price.raw_price;
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
  includeAddOn.value = false;
});

watch(quantity, () => {
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
    quantity.value,
    includeAddOn.value,
  );
});

watch(includeAddOn, () => {
  prepareAddBasketForm(
    props.product.id,
    (<ShopProductVariant>selectedVariant.value).id,
    quantity.value,
    includeAddOn.value,
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

      <div v-if="availableQuantity && availableQuantity > 0 && product.add_ons">
        <label
          class="block text-base leading-6 font-semibold text-primary-dark sm:max-xl:text-lg xl:text-xl"
        >
          Add {{ product.add_ons.name }}
        </label>

        <div
          class="flex w-full flex-col justify-between space-y-4 rounded-md border border-grey-off p-2 text-base text-gray-900 shadow-xs xs:flex-row xs:items-start xs:space-y-0 xs:space-x-2 md:text-lg"
        >
          <div class="flex-1">
            Include a digital PDF of your travel card delivered straight to your
            email once your order is complete.
          </div>

          <div
            class="border-gray-off flex w-full items-center justify-end space-x-2 border-t pt-4 xs:w-auto xs:border-t-0 xs:pt-0"
          >
            <span
              :class="includeAddOn ? 'text-lg font-semibold text-green' : ''"
              v-text="`+${product.add_ons.price.current_price}`"
            />

            <FormCheckbox
              v-model="includeAddOn"
              name="include-add-on"
              label=""
              xl
              hide-label
            />
          </div>
        </div>
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
    </form>
  </div>
</template>
