<script setup lang="ts">
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useAddToBasket from '@/composables/useAddToBasket';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { ShopDigitalCard } from '@/types/Shop';
import Modal from '@/Components/Overlays/Modal.vue';
import { ref } from 'vue';

const props = defineProps<{ product: ShopDigitalCard }>();

const {
  addBasketForm,
  prepareAddBasketForm,
  prepareAddBasketDigitalCardForm,
  submitAddBasketForm,
} = useAddToBasket();

const showModal = ref(false);

prepareAddBasketDigitalCardForm(props.product.id);

const addToBasket = () => {
  useJourneyTracking().logEvent('clicked', 'ShopProductCard/AddToBasket', {
    digitalCard: true,
    title: props.product.title,
    id: props.product.id,
    includedPhysicalProduct: addBasketForm.product_id !== null,
  });

  showModal.value = false;

  submitAddBasketForm({
    only: ['basket'],
  });
};

const includePhysicalProduct = () => {
  if (!props.product.product) {
    return;
  }

  prepareAddBasketForm(
    props.product.product.id,
    props.product.product.variantId,
  );

  addToBasket();
};

const prepareAddToBasket = () => {
  if (!props.product.product || props.product.product.isInBasket) {
    addToBasket();

    return;
  }

  showModal.value = true;
};
</script>

<template>
  <CoeliacButton
    as="button"
    type="button"
    label="Add to Basket"
    classes="justify-center text-center"
    theme="secondary"
    size="lg"
    :icon="ShoppingBagIcon"
    icon-position="right"
    bold
    :loading="addBasketForm.processing"
    @click="prepareAddToBasket()"
  />

  <Modal
    v-if="product.product"
    :open="showModal"
    size="relaxed"
    overlay-classes="!z-[9999999999]"
    @close="() => (showModal = false)"
  >
    <template #header>
      <h3 class="pl-3 text-lg font-semibold lg:text-xl">
        Add {{ product.title }} to your basket
      </h3>
    </template>

    <template #default>
      <ul class="divide-grey-lighter mx-2 divide-y">
        <li class="flex justify-between py-4">
          <div class="flex flex-col space-y-1">
            <p class="text-lg font-semibold">Digital Card Only</p>

            <p
              class="text-xl font-semibold text-primary-dark lg:text-2xl"
              v-text="product.price.default"
            />

            <p class="mt-4 text-sm text-grey-dark">
              My {{ product.title }} digital card only.
            </p>
          </div>

          <div class="ml-4 shrink-0">
            <CoeliacButton
              as="button"
              type="button"
              label="Add to Basket"
              classes="justify-center text-center px-8"
              theme="secondary"
              size="md"
              bold
              @click="addToBasket()"
            />
          </div>
        </li>

        <li class="flex justify-between py-4">
          <div class="flex flex-col space-y-1">
            <p class="text-lg font-semibold">
              Digital Card and {{ product.product.title }}
            </p>

            <p
              class="text-xl font-semibold text-primary-dark lg:text-2xl"
              v-text="product.price.bundle"
            />

            <p class="text-sm font-semibold">
              Saving {{ product.price.saving }} if bought separately.
            </p>

            <p class="mt-4 text-sm text-grey-dark">
              My {{ product.title }} digital card, along with my
              <a
                :href="product.product.link"
                target="_blank"
                class="cursor-pointer font-semibold text-primary-dark transition hover:text-black"
                v-text="product.product.title"
              />
              physical product.
            </p>
          </div>

          <div class="ml-4 shrink-0">
            <CoeliacButton
              as="button"
              type="button"
              label="Add to Basket"
              classes="justify-center text-center px-8"
              theme="secondary"
              size="md"
              bold
              @click="includePhysicalProduct()"
            />
          </div>
        </li>
      </ul>
    </template>
  </Modal>
</template>
