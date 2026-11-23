<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShopBasketItem } from '@/types/Shop';
import DigitalLineItem from '@/Components/PageSpecific/Shop/OrderComplete/DigitalLineItem.vue';

defineProps<{ product: ShopBasketItem }>();
</script>

<template>
  <li>
    <div class="flex space-x-2 py-3 sm:space-x-4 sm:py-4">
      <img
        :src="product.image"
        :alt="product.title"
        class="size-16 flex-none rounded-md object-cover object-center xs:size-20 sm:size-24"
      />

      <div class="flex-auto space-y-1">
        <h3
          class="font-semibold text-primary-dark sm:max-lg:text-lg lg:text-xl"
        >
          <Link
            :href="product.link"
            target="_blank"
          >
            {{ product.title }}
          </Link>
        </h3>
        <p
          v-if="product.variant"
          class="text-grey-darker"
          v-text="product.variant"
        />
        <p class="text-grey-darker">Quantity: {{ product.quantity }}</p>
      </div>

      <p
        class="flex-none font-semibold"
        v-text="product.line_price"
      />
    </div>

    <ul
      v-if="product.digitalBundles?.length"
      class="ml-16 pl-4 xs:ml-20 sm:ml-24"
    >
      <li class="my-2 border-b border-primary-dark pb-1 font-semibold">
        Digital Bundles
      </li>

      <DigitalLineItem
        v-for="digitalItem in product.digitalBundles"
        :key="digitalItem.id"
        :product="digitalItem"
        compact
        :skip-bundle-rows="false"
      />
    </ul>
  </li>
</template>
