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

const props = defineProps<{ product: ShopProductDetail }>();

const selectedVariant: Ref<ShopProductVariant | undefined> = ref();
const quantity: Ref<number> = ref(1);
const isInStock: Ref<boolean> = ref(true);

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

onMounted(() => {
  if (props.product.variants.length === 1) {
    // eslint-disable-next-line prefer-destructuring
    selectedVariant.value = props.product.variants[0];
    prepareAddBasketForm(props.product.id, selectedVariant.value.id);
  }

  checkStock();
});

watch(selectedVariant, () => {
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
    class="mt-3 w-full md:col-start-1 md:row-start-2 md:max-w-sm md:self-start"
  >
    <form
      class="flex w-full flex-col space-y-3"
      @submit.prevent="addToBasket()"
    >
      <div
        v-if="product.variants.length > 1"
        class="w-full sm:flex sm:justify-between"
      >
        <RadioGroup
          v-model="selectedVariant"
          class="w-full"
        >
          <label
            class="block text-base leading-6 font-semibold text-primary-dark md:max-xl:text-lg xl:text-xl"
          >
            Select {{ product.variant_title }}
            <span
              class="text-red"
              v-text="'*'"
            />
          </label>
          <div class="mt-1 grid w-full grid-cols-1 gap-3 xxs:grid-cols-2">
            <RadioGroupOption
              v-for="variant in product.variants"
              :key="variant.id"
              v-slot="{ checked, disabled }"
              as="template"
              :value="variant"
              :disabled="variant.quantity === 0"
            >
              <div
                :class="[
                  checked
                    ? 'bg-primary-light/50 font-semibold ring-2 ring-primary'
                    : 'ring-0',
                  disabled ? 'border-grey-off/30' : 'border-grey-off',
                  'relative block cursor-pointer rounded-lg border p-3 outline-hidden',
                ]"
              >
                <RadioGroupLabel
                  as="div"
                  class="flex items-center space-x-2 text-base leading-none text-gray-900"
                >
                  <Icon
                    v-if="variant.icon"
                    :name="variant.icon.component"
                    :style="{ color: variant.icon.color }"
                  />

                  <div class="flex flex-1 justify-between">
                    <span
                      :class="{ 'text-grey-off': disabled }"
                      v-text="variant.title"
                    />
                    <span
                      v-if="disabled"
                      class="text-xs text-grey-dark italic"
                      v-text="'Out of stock'"
                    />
                  </div>
                </RadioGroupLabel>

                <RadioGroupDescription
                  v-if="false"
                  as="p"
                  class="mt-1 text-sm text-gray-500"
                >
                  {{ variant.title }}
                </RadioGroupDescription>
              </div>
            </RadioGroupOption>
          </div>
        </RadioGroup>
      </div>

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
