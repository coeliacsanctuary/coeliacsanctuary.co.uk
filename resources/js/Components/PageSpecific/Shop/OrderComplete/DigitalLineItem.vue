<script setup lang="ts">
import { ShopBasketDigitalItem } from '@/types/Shop';
import CountryFlag from '@/Components/CountryFlag.vue';

withDefaults(
  defineProps<{
    product: ShopBasketDigitalItem;
    compact?: boolean;
    skipBundleRows?: boolean;
  }>(),
  { compact: false, skipBundleRows: true },
);
</script>

<template>
  <li
    v-if="
      (product.hasBundleInBasket === false && skipBundleRows === true) ||
      skipBundleRows === false
    "
  >
    <div class="flex space-x-2 py-3 sm:space-x-4 sm:py-4">
      <div
        class="flex flex-none items-center justify-center"
        :class="compact ? 'mr-0 w-16' : 'w-16 xs:w-20 sm:w-24'"
      >
        <CountryFlag
          :country="product.country.label"
          :code="product.country.code"
          class="mx-auto object-cover"
          :width="compact ? '40' : '72'"
          :height="compact ? '30' : '54'"
        />
      </div>

      <div class="flex-auto space-y-1">
        <h3
          class="font-semibold text-primary-dark sm:max-lg:text-lg lg:text-xl"
          v-text="product.title"
        />
      </div>

      <p
        class="flex-none font-semibold"
        v-text="product.item_price"
      />
    </div>
  </li>
</template>
