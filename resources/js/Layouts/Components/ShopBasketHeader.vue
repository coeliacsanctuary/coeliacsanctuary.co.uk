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
        You have <strong v-text="items.length" /> <span v-text="label" /> in
        your basket
      </div>
      <div
        class="prose prose-lg font-semibold xl:prose-xl hover:text-primary-dark cursor-pointer"
        @click="openBasket()"
      >
        View Basket
      </div>
    </div>
  </div>
</template>
