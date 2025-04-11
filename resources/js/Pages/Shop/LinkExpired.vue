<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShopProductIndex } from '@/types/Shop';
import SubHeading from '@/Components/SubHeading.vue';
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';

const props = defineProps<{ products: ShopProductIndex[] }>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{ href: '/shop', label: 'Start shopping!' }"
      :border="false"
    >
      Basket Expired!
    </Heading>
  </Card>

  <Card class="flex flex-col items-center justify-center space-y-4 py-8">
    <p class="prose prose-xl text-center font-semibold">
      Sorry, your basket has expired, to checkout, please add your items to your
      basket again.
    </p>

    <CoeliacButton
      :as="Link"
      href="/shop"
      label="Back to shop"
      size="xxl"
      theme="secondary"
    />
  </Card>

  <Card class="flex flex-col items-center justify-center space-y-4 py-8">
    <SubHeading> Here's some products to get you started! </SubHeading>

    <div class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <CategoryProductCard
        v-for="(product, index) in products"
        :key="product.id"
        :product="product"
        :class="{ 'hidden xl:flex': index >= 3, 'sm:flex': index === 3 }"
      />
    </div>
  </Card>
</template>
