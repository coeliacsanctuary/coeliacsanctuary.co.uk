<script setup lang="ts">
import { CreditCardIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { ShopBasketItem } from '@/types/Shop';
import { ref } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useGoogleEvents from '@/composables/useGoogleEvents';
import QuantitySwitcher from '@/Components/PageSpecific/Shop/Checkout/QuantitySwitcher.vue';
import Loader from '@/Components/Loader.vue';

defineProps<{ items: ShopBasketItem[]; subtotal: string }>();

defineEmits(['close']);

const deletingItem = ref<null | number>(null);
const deletingAddOn = ref<null | number>(null);

const removeItem = (item: ShopBasketItem) => {
  deletingItem.value = item.id;

  router.delete(`/shop/basket/${item.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      useGoogleEvents().googleEvent('event', 'remove-item-from-cart', {
        itemId: item.id,
      });
    },
  });
};

const removeAddOn = (item: ShopBasketItem) => {
  if (!item.add_on) {
    return;
  }

  deletingAddOn.value = item.id;

  router.delete(`/shop/basket/${item.id}/add-on`, {
    preserveScroll: true,
  });
};

const loadingItem = ref<null | number>(null);
const hasError = ref<false | number>(false);

const alterQuantity = (
  item: ShopBasketItem,
  action: 'increase' | 'decrease',
) => {
  hasError.value = false;
  loadingItem.value = item.id;

  router.patch(
    '/shop/basket',
    {
      action,
      item_id: item.id,
    },
    {
      preserveScroll: true,
      only: ['basket', 'has_basket', 'payment_intent'],
      onFinish: () => {
        loadingItem.value = null;
      },
      onError: (e) => {
        if (e?.quantity) {
          hasError.value = item.id;
        }
      },
    },
  );
};
</script>

<template>
  <div class="flex-1 p-4">
    <div class="flow-root">
      <ul class="-my-3 divide-y divide-gray-200">
        <template
          v-for="item in items"
          :key="item.id"
        >
          <li class="relative">
            <Loader
              :display="loadingItem === item.id"
              absolute
              on-top
              blur
              color="dark"
              size="size-12"
              width="border-8"
            />

            <div
              class="relative flex py-3"
              :class="{ '!border-b-0': !!item.add_on }"
            >
              <div
                class="hidden h-24 w-24 shrink-0 overflow-hidden rounded-md border border-gray-200 sm:block"
              >
                <img
                  :src="item.image"
                  :alt="item.title"
                  class="h-full w-full object-cover object-center"
                />
              </div>

              <div class="flex flex-1 flex-col sm:ml-4">
                <div>
                  <div
                    class="flex justify-between text-base font-medium text-gray-900"
                  >
                    <h3>
                      <Link
                        :href="item.link"
                        class="font-semibold hover:text-primary-dark"
                      >
                        {{ item.title }}
                      </Link>
                    </h3>
                    <p
                      class="ml-4"
                      v-text="item.line_price"
                    />
                  </div>

                  <p
                    v-if="item.variant !== ''"
                    class="mt-1 text-sm text-gray-500"
                    v-text="item.variant"
                  />
                </div>

                <div class="flex flex-1 items-center justify-between">
                  <div class="flex flex-1 items-center space-x-2">
                    <p>Quantity</p>

                    <QuantitySwitcher
                      :quantity="item.quantity"
                      @alter="(mode) => alterQuantity(item, mode)"
                    />
                  </div>

                  <CoeliacButton
                    theme="faded"
                    icon-only
                    :icon="TrashIcon"
                    size="xxl"
                    as="button"
                    type="button"
                    classes="p-1! hover:text-primary-dark !shadow-none"
                    :loading="deletingItem === item.id"
                    @click="removeItem(item)"
                  />
                </div>

                <span
                  v-if="hasError === item.id"
                  class="text-sm font-semibold text-red"
                >
                  Sorry, there isn't enough quantity available...
                </span>
              </div>
            </div>
            <div
              v-if="item.add_on"
              class="relative -mt-1 flex pb-3"
            >
              <div class="hidden w-24 shrink-0 sm:block" />

              <div class="flex flex-1 flex-col sm:ml-4">
                <div>
                  <div
                    class="flex items-center justify-between text-base font-medium text-gray-900"
                  >
                    <h3 v-text="item.add_on.title" />

                    <div class="flex items-center space-x-4">
                      <p
                        class="ml-4"
                        v-text="`+${item.add_on.price}`"
                      />

                      <CoeliacButton
                        theme="faded"
                        icon-only
                        :icon="TrashIcon"
                        size="xxl"
                        as="button"
                        type="button"
                        classes="p-1! hover:text-primary-dark !shadow-none"
                        :loading="deletingAddOn === item.id"
                        @click="removeAddOn(item)"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </template>
      </ul>
    </div>
  </div>

  <div class="border-t border-gray-200 p-4">
    <div class="flex justify-between text-base font-semibold">
      <p>Subtotal</p>
      <p
        class="text-xl"
        v-text="subtotal"
      />
    </div>

    <p class="mt-0.5 text-sm">Postage price calculated at checkout.</p>

    <div class="mt-6 flex">
      <CoeliacButton
        :as="Link"
        href="/shop/basket"
        label="Checkout"
        size="xxl"
        :icon="CreditCardIcon"
        icon-position="right"
        bold
        classes="mx-auto"
        @click="$emit('close')"
      />
    </div>

    <div class="mt-6 flex justify-center text-center text-sm">
      <p>
        or{{ ' ' }}
        <button
          type="button"
          class="font-semibold text-primary hover:text-primary-dark"
          @click="$emit('close')"
        >
          Continue Shopping
          <span aria-hidden="true"> &rarr;</span>
        </button>
      </p>
    </div>
  </div>
</template>
