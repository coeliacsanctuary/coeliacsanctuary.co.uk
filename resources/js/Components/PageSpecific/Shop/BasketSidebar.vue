<script setup lang="ts">
import { CreditCardIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { BasketProp } from '@/types/Shop';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import ProductLineItem from '@/Components/PageSpecific/Shop/BasketSideBar/ProductLineItem.vue';
import DigitalLineItem from '@/Components/PageSpecific/Shop/BasketSideBar/DigitalLineItem.vue';

defineProps<BasketProp>();

defineEmits(['close']);
</script>

<template>
  <div class="flex-1 p-4">
    <div class="flow-root">
      <ul class="-my-3 divide-y divide-gray-200">
        <ProductLineItem
          v-for="item in items"
          :key="item.id"
          :item="item"
        />

        <DigitalLineItem
          v-for="item in digitalItems"
          :key="item.id"
          :item="item"
        />
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
