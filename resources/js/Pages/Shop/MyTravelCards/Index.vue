<script setup lang="ts">
import CoeliacTravelCards from '@/Layouts/CoeliacTravelCards.vue';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { PurchasedCardIndex } from '@/types/Shop';
import PurchasedCard from '@/Components/PageSpecific/Shop/MyTravelCards/PurchasedCard.vue';

defineOptions({ layout: CoeliacTravelCards });

defineProps<{ cards: PurchasedCardIndex[] }>();
</script>

<template>
  <Card>
    <Heading :border="false">My Travel Cards</Heading>
  </Card>

  <Card class="flex flex-col space-y-4">
    <p class="prose prose-lg max-w-none">
      Your digital travel cards are all in one place, ready whenever you need
      them. Open a card below to view the full translation and show it to
      restaurant or hotel staff while you're travelling.
    </p>
    <p class="prose prose-lg max-w-none">
      For quicker access when you're out and about, you can also add your cards
      to Apple Wallet or Google Wallet on supported devices. Your cards will
      stay available here too, so you can come back and view them in your
      browser at any time.
    </p>
    <p class="prose prose-lg max-w-none">
      If we make any important updates to the wording of a card you've
      purchased, we'll keep the version available to you up to date.
    </p>
  </Card>

  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <PurchasedCard
      v-for="card in cards"
      :key="card.slug"
      :card="card"
    />
  </div>
</template>
