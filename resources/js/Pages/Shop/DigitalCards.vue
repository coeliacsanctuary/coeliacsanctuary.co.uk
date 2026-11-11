<script setup lang="ts">
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { ShopDigitalCard } from '@/types/Shop';
import DigitalProductCard from '@/Components/PageSpecific/Shop/DigitalCards/DigitalProductCard.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import TravelCardImportantInformation from '@/Components/PageSpecific/Shop/TravelCardImportantInformation.vue';
import CollapsibleCard from '@/Components/CollapsibleCard.vue';
import { InformationCircleIcon } from '@heroicons/vue/20/solid';

defineProps<{
  manageLink: string;
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

      <Card class="flex flex-col space-y-3">
        <SubHeading text-size="small"> Already own digital cards? </SubHeading>

        <p class="prose mt-4 max-w-none">
          If you already own some digital travel cards, you can access them
          anywhere, instantly by logging in below.
        </p>

        <CoeliacButton
          label="See my Digital Travel Cards"
          as="a"
          :href="manageLink"
          target="_blank"
          bold
          theme="secondary"
          size="xl"
          classes="justify-center"
        />
      </Card>

      <CollapsibleCard show-breakpoint="xmd">
        <template #title> How do your Digital Travel Cards work? </template>

        <div class="flex flex-col space-y-4">
          <p class="prose max-w-none">
            When you purchase a digital travel card, you will be emailed a link
            to view your cards.
          </p>
          <p class="prose max-w-none">
            You can then access your travel cards at any time, on any device, to
            show staff when eating out.
          </p>
          <p class="prose max-w-none">
            My Digital Cards can be used in your web browser, or added to your
            Google Wallet or Apple Wallet app on your phone.
          </p>
        </div>
      </CollapsibleCard>

      <CollapsibleCard show-breakpoint="xmd">
        <template #title>
          <span class="flex items-center gap-1.5">
            <InformationCircleIcon class="size-5 shrink-0 text-primary-dark" />
            Before you buy
          </span>
        </template>

        <TravelCardImportantInformation
          :border="false"
          :heading="false"
          text-classes="prose max-w-none"
        />
      </CollapsibleCard>
    </template>
  </SidebarLayout>
</template>
