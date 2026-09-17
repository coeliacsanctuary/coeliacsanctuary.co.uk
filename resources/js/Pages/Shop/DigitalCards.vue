<script setup lang="ts">
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { ShopDigitalCard } from '@/types/Shop';
import DigitalProductCard from '@/Components/PageSpecific/Shop/DigitalProductCard.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{
  type: string;
  categoryLink: string;
  products: ShopDigitalCard[];
}>();
</script>

<template>
  <Card>
    <Heading
      :border="false"
      :back-link="{ label: 'Back to Shop', href: '/shop' }"
    >
      {{ type }}
    </Heading>
  </Card>

  <SidebarLayout>
    <div class="mx-auto flex w-full flex-col space-y-4">
      <DigitalProductCard
        v-for="(product, index) in products"
        :key="product.slug"
        :product="product"
        :eager="index < 3"
      />
    </div>

    <template #sidebar>
      <Card class="flex flex-col space-y-3">
        <SubHeading text-size="small">
          What are Digital Travel Cards
        </SubHeading>

        <p class="prose mt-4 max-w-none">
          If you don't want, or need a
          <a :href="categoryLink">physical travel card</a> for your holiday, or
          you need one urgently, my digital cards are the next best thing!
        </p>

        <p class="prose max-w-none">
          With my digital cards, you can buy instantly, and view them any time
          right on your phone.
        </p>
      </Card>
    </template>
  </SidebarLayout>
</template>
