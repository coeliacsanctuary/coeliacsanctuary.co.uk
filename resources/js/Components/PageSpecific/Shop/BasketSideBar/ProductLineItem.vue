<script setup lang="ts">
import { TrashIcon } from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import Loader from '@/Components/Loader.vue';
import QuantitySwitcher from '@/Components/PageSpecific/Shop/Checkout/QuantitySwitcher.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShopBasketItem } from '@/types/Shop';
import { ref } from 'vue';
import useGoogleEvents from '@/composables/useGoogleEvents';
import useJourneyTracking from '@/composables/useJourneyTracking';
import DigitalLineItem from '@/Components/PageSpecific/Shop/BasketSideBar/DigitalLineItem.vue';

const props = defineProps<{ item: ShopBasketItem }>();

const hasError = ref(false);
const isLoading = ref(false);
const isDeletingItem = ref(false);

const removeItem = () => {
  isDeletingItem.value = true;

  router.delete(`/shop/basket/${props.item.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      useGoogleEvents().googleEvent('event', 'remove-item-from-cart', {
        itemId: props.item.id,
      });

      useJourneyTracking().logEvent('clicked', 'ShopBasketSideBar/DeleteItem', {
        title: props.item.title,
      });
    },
  });
};

const alterQuantity = (action: 'increase' | 'decrease') => {
  hasError.value = false;
  isLoading.value = true;

  useJourneyTracking().logEvent('clicked', 'ShopBasketSideBar/AlterQuantity', {
    title: props.item.title,
    action,
  });

  router.patch(
    '/shop/basket',
    {
      action,
      item_id: props.item.id,
    },
    {
      preserveScroll: true,
      only: ['basket', 'has_basket', 'payment_intent'],
      onFinish: () => {
        isLoading.value = false;
      },
      onError: (e) => {
        if (e?.quantity) {
          hasError.value = true;
        }
      },
    },
  );
};
</script>

<template>
  <li class="relative">
    <Loader
      :display="isLoading"
      absolute
      on-top
      blur
      color="dark"
      size="size-12"
      width="border-8"
    />

    <div class="relative flex py-3">
      <div
        class="hidden size-24 shrink-0 overflow-hidden rounded-md border border-gray-200 sm:block"
      >
        <img
          :src="item.image"
          :alt="item.title"
          class="aspect-square object-cover"
        />
      </div>

      <div class="flex flex-1 flex-col sm:ml-4">
        <div>
          <div class="flex justify-between text-base font-medium text-gray-900">
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
              @alter="(mode) => alterQuantity(mode)"
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
            :loading="isDeletingItem"
            @click="removeItem()"
          />
        </div>

        <span
          v-if="hasError"
          class="text-sm font-semibold text-red"
        >
          Sorry, there isn't enough quantity available...
        </span>
      </div>
    </div>

    <ul
      v-if="item.digitalBundles?.length"
      class="-mt-3 ml-28"
    >
      <li class="my-2 border-b border-primary-dark pb-1 font-semibold">
        Digital Bundles
      </li>

      <DigitalLineItem
        v-for="digitalItem in item.digitalBundles"
        :key="digitalItem.id"
        :item="digitalItem"
        compact
        :skip-bundle-rows="false"
      />
    </ul>
  </li>
</template>
