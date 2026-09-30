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

  <Card>
    <p class="prose prose-lg max-w-none">Some intro paragraph...</p>
  </Card>

  <div class="grid gap-6 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
    <PurchasedCard
      v-for="card in cards"
      :key="card.slug"
      :card="card"
    />
  </div>
</template>
