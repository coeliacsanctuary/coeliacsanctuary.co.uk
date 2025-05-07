<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Page } from '@inertiajs/core';
import { ShopBasketItem } from '@/types/Shop';
import { pluralise } from '@/helpers';
import EventBus from '@/eventBus';

const page: Page<{ basket?: { items: ShopBasketItem[] } }> = usePage();
const items = computed(() => page.props.basket?.items || []);
const label = pluralise('item', items.value.length);

const openBasket = () => {
  EventBus.$emit('open-basket');
};

const totalItems = computed(() => {
  let total = 0;

  page.props.basket?.items.forEach((item) => {
    total += item.quantity;
  });

  return total;
});

EventBus.$on('product-added-to-basket', () => {
  router.reload({
    only: ['basket'],
  });
});
</script>

<template>
  <div
    id="header-basket-detail"
    class="border border-primary-light bg-primary-light/50"
  >
    <div
      class="mx-auto flex max-w-8xl flex-col items-center justify-between p-2 xs:flex-row sm:p-4"
    >
      <div class="prose prose-lg xl:prose-xl">
        You have <strong v-text="totalItems" /> <span v-text="label" /> in your
        basket
      </div>
      <div
        class="prose prose-lg cursor-pointer font-semibold hover:text-primary-dark xl:prose-xl"
        @click="openBasket()"
      >
        View Basket
      </div>
    </div>
  </div>
</template>
