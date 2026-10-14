<script lang="ts" setup>
import {
  ShopCategoryIndex,
  TravelCardDestinationChip,
  TravelCardDigitalType,
  TravelCardReviewSummary,
  TravelCardSearchResult,
} from '@/types/Shop';
import TravelCardHero from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardHero.vue';
import TravelCardSearchResults from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardSearchResults.vue';
import TravelCardNoResults from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardNoResults.vue';
import TravelCardSearchSkeleton from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardSearchSkeleton.vue';
import TravelCardTrustFacts from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardTrustFacts.vue';
import TravelCardComparison from '@/Components/PageSpecific/Shop/TravelCardLandingPage/TravelCardComparison.vue';
import ShopCustomerReviews from '@/Components/PageSpecific/Shop/ShopCustomerReviews.vue';
import ShopDeliveryFacts from '@/Components/PageSpecific/Shop/ShopDeliveryFacts.vue';
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import { ArticleFaq } from '@/types/Types';
import { ref } from 'vue';

defineProps<{
  searchTerm: string | null;
  search: TravelCardSearchResult | null;
  destinations: TravelCardDestinationChip[];
  categories: ShopCategoryIndex[];
  digitalCardTypes: TravelCardDigitalType[];
  reviews: TravelCardReviewSummary;
}>();

const searching = ref(false);

const faqs: ArticleFaq[] = [
  {
    question: "I'm visiting two countries, do I need two cards?",
    answer:
      "Search both at once — try <strong>Spain and France</strong> — and I'll show you what I've got for each. Every one of my standard cards carries two languages, so quite often a single card covers both stops. If it doesn't, my Full Set covers every language I make.",
  },
  {
    question: "What if the language I need isn't listed?",
    answer:
      "Search by country rather than language and I'll show you everything that would work there, including cards in a language that's widely understood locally even if it isn't the official one. If there's genuinely nothing, do get in touch — new languages usually get added because somebody asked.",
  },
  {
    question: 'Physical or digital, which one do I need?',
    answer:
      "The text is identical — the same professional translation either way, just presented differently. A physical card is thick printed card with a language on each side, so there's nothing to go flat at the table. A digital card is the same thing on your phone, in your browser or in Apple or Google Wallet, and it's yours the moment you've paid. Pick digital if you're travelling soon or you'd rather not carry anything, and if you're buying a physical card anyway you can add the digital version for a lot less than buying it on its own.",
  },
  {
    question:
      'I have other allergies as well as coeliac, which card do I need?',
    answer:
      'My Coeliac+ cards. One side explains coeliac disease and cross contamination, and the reverse has a tick box list so you can flag other allergens or dietary needs like vegetarian or vegan.',
  },
  {
    question: "What's actually written on the card?",
    answer:
      "An explanation of coeliac disease and a gluten free diet, which foods are safe and which aren't, the risk of cross contamination, and the specific things kitchen staff need to watch out for. You can read the full English translation on any product page.",
  },
  {
    question: 'How long does delivery take?',
    answer:
      "Physical cards are dispatched within two working days and go First Class, with Royal Mail aiming to deliver most of them the next working day. Digital cards aren't posted at all — you'll get a link to view them as soon as you've paid, so they're the answer if you're flying sooner than the post will manage.",
  },
];
</script>

<template>
  <TravelCardHero
    v-model:searching="searching"
    :destinations="destinations"
    :search-term="searchTerm"
  />

  <TravelCardSearchSkeleton v-if="searching" />

  <TravelCardSearchResults
    v-else-if="search"
    :search="search"
  />

  <TravelCardNoResults
    v-else-if="searchTerm"
    :search-term="searchTerm"
  />

  <TravelCardTrustFacts :reviews="reviews" />

  <TravelCardComparison
    :categories="categories"
    :digital-card-types="digitalCardTypes"
  />

  <FaqCard
    :faqs="faqs"
    title="Common questions about my travel cards"
  />

  <ShopCustomerReviews
    v-if="reviews.reviews.length"
    :reviews="reviews.reviews"
  />

  <ShopDeliveryFacts />
</template>
