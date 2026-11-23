<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';
import TravelCardDestinationGrid from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardDestinationGrid.vue';
import TravelCardDigitalCardRow from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardDigitalCardRow.vue';
import { ShopDigitalCard, TravelCardSearchResult } from '@/types/Shop';
import { computed } from 'vue';

const props = defineProps<{
  search: TravelCardSearchResult;
}>();

const names = computed((): string => {
  const terms = props.search.destinations.map(
    (destination) => destination.term,
  );

  if (terms.length < 2) {
    return terms.join('');
  }

  return `${terms.slice(0, -1).join(', ')} and ${terms[terms.length - 1]}`;
});

const isSingleDestination = computed(
  (): boolean => props.search.destinations.length === 1,
);

const digitalCards = computed(
  (): ShopDigitalCard[] => props.search.destinations[0]?.digital_cards ?? [],
);
</script>

<template>
  <Card class="space-y-4">
    <SubHeading
      as="h2"
      text-size="small"
      classes="flex flex-wrap items-center gap-x-3 gap-y-1"
    >
      <span class="inline-flex items-center gap-2">
        <template
          v-for="destination in search.destinations"
          :key="destination.term"
        >
          <img
            v-if="destination.flag"
            :src="`https://flagcdn.com/24x18/${destination.flag}.png`"
            :alt="`${destination.term} flag`"
            width="24"
            height="18"
            class="h-[18px] w-6 shrink-0 object-cover"
          />
        </template>
      </span>

      <span>Travel cards for {{ names }}</span>
    </SubHeading>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <div
      v-if="search.covers_all.length"
      class="space-y-3 rounded-sm border border-primary-light/60 bg-primary-lightest/40 p-4"
    >
      <SubHeading
        as="h3"
        text-size="xs"
        classes="text-primary-dark"
      >
        Good news — one card covers
        {{ isSingleDestination ? 'it' : 'them all' }}
      </SubHeading>

      <p class="prose max-w-none md:prose-lg">
        You don't need one for each. These cover {{ names }} on a single card.
      </p>

      <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
        <CategoryProductCard
          v-for="product in search.covers_all"
          :key="product.link"
          :product="product"
          eager
        />
      </div>
    </div>

    <p
      v-else-if="!isSingleDestination"
      class="prose max-w-none md:prose-lg"
    >
      No single card covers all of them, so here's what I've got for each.
    </p>

    <template v-if="isSingleDestination">
      <div
        v-for="destination in search.destinations"
        :key="destination.term"
        class="space-y-3"
      >
        <p
          v-if="destination.type === 'language'"
          class="prose max-w-none md:prose-lg"
        >
          These cards can be used anywhere
          <strong v-text="destination.term" /> is spoken.
        </p>

        <TravelCardDestinationGrid :products="destination.products" />
      </div>
    </template>
  </Card>

  <template v-if="!isSingleDestination">
    <Card
      v-for="destination in search.destinations"
      :key="destination.term"
      class="space-y-4"
    >
      <SubHeading
        as="h2"
        text-size="small"
        classes="flex flex-wrap items-center gap-x-3 gap-y-1"
      >
        <img
          v-if="destination.flag"
          :src="`https://flagcdn.com/24x18/${destination.flag}.png`"
          :alt="`${destination.term} flag`"
          width="24"
          height="18"
          class="h-[18px] w-6 shrink-0 object-cover"
        />

        <span>For {{ destination.term }}</span>
      </SubHeading>

      <div
        class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
      />

      <TravelCardDestinationGrid :products="destination.products" />

      <div
        v-if="destination.digital_cards.length"
        class="space-y-3"
      >
        <SubHeading
          as="h3"
          text-size="xs"
          classes="text-primary-dark"
        >
          Rather have it on your phone?
        </SubHeading>

        <TravelCardDigitalCardRow
          v-for="card in destination.digital_cards"
          :key="card.id"
          :card="card"
        />
      </div>
    </Card>
  </template>

  <Card
    v-if="isSingleDestination && digitalCards.length"
    class="space-y-4"
  >
    <SubHeading
      as="h2"
      text-size="small"
    >
      Rather have it on your phone?
    </SubHeading>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <p class="prose max-w-none md:prose-lg">
      My digital cards are available instantly, and work on your phone wherever
      you are.
    </p>

    <div class="space-y-3">
      <TravelCardDigitalCardRow
        v-for="card in digitalCards"
        :key="card.id"
        :card="card"
      />
    </div>
  </Card>
</template>
