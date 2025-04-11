<script setup lang="ts">
import { ShopBasketItem } from '@/types/Shop';
import { TrashIcon } from '@heroicons/vue/24/outline';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuantitySwitcher from '@/Components/PageSpecific/Shop/Checkout/QuantitySwitcher.vue';
import Loader from '@/Components/Loader.vue';
import eventBus from '@/eventBus';

defineProps<{ items: ShopBasketItem[] }>();

const deletingItem = ref<null | number>(null);
const loadingItem = ref<null | number>(null);
const hasError = ref<false | number>(false);

const alterQuantity = (
  item: ShopBasketItem,
  action: 'increase' | 'decrease',
) => {
  loadingItem.value = item.id;
  hasError.value = false;

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
        eventBus.$emit('refresh-payment-element');
      },
      onError: (e) => {
        if (e?.quantity) {
          hasError.value = item.id;
        }
      },
    },
  );
};

const removeItem = (item: ShopBasketItem) => {
  deletingItem.value = item.id;

  router.delete(`/shop/basket/${item.id}`, {
    preserveScroll: true,
    only: ['basket', 'has_basket', 'payment_intent'],
    onSuccess: () => {
      eventBus.$emit('refresh-payment-element');
    },
  });
};
</script>

<template>
  <div class="flow-root">
    <ul class="-my-3 divide-y divide-gray-200">
      <li
        v-for="item in items"
        :key="item.id"
        class="relative flex space-x-3 py-3"
      >
        <Loader
          :display="loadingItem === item.id"
          absolute
          on-top
          blur
          color="secondary"
          size="size-12"
          width="border-8"
        />
        <div
          class="h-24 w-24 shrink-0 overflow-hidden rounded-md border border-gray-200"
        >
          <img
            :src="item.image"
            :alt="item.title"
            class="h-full w-full object-cover object-center"
          />
        </div>

        <div class="ml-4 flex flex-1 flex-col">
          <div>
            <div class="flex justify-between text-base">
              <h3>
                <Link
                  :href="item.link"
                  class="font-semibold hover:text-primary-dark"
                >
                  {{ item.title }}
                </Link>
              </h3>
              <p
                class="ml-4 text-xl font-semibold"
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
            <div class="flex flex-1 items-center space-x-1">
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
              classes="p-1! hover:text-primary-dark shadow-none"
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
      </li>
    </ul>
  </div>
</template>
