<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { TravelCardReviewSummary } from '@/types/Shop';
import {
  DocumentDuplicateIcon,
  LanguageIcon,
  ShieldCheckIcon,
  StarIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import { pluralise } from '@/helpers';
import { FunctionalComponent } from 'vue';

const props = defineProps<{
  reviews: TravelCardReviewSummary;
}>();

type Fact = {
  icon: FunctionalComponent;
  title: string;
  description: string;
};

const facts = computed((): Fact[] => {
  const rated: Fact = props.reviews.average
    ? {
        icon: StarIcon,
        title: `Rated ${props.reviews.average} out of 5`,
        description: `From ${props.reviews.count} verified ${pluralise('review', props.reviews.count)} left by customers after their order shipped.`,
      }
    : {
        icon: StarIcon,
        title: 'Sold worldwide',
        description:
          "I've sent these all over the world, and every customer is invited to review their order once it's on its way.",
      };

  return [
    {
      icon: LanguageIcon,
      title: 'Professionally translated',
      description:
        'Every card is translated by a native speaker, never by Google Translate, so what it says is accurate and easy to follow.',
    },
    {
      icon: ShieldCheckIcon,
      title: 'Built to last',
      description:
        "Printed on ultra thick 3mm card, so it'll survive being pulled out of your bag at every restaurant on your trip.",
    },
    {
      icon: DocumentDuplicateIcon,
      title: 'Both sides used',
      description:
        'My standard cards carry two languages, one per side. My Coeliac+ cards use the reverse for allergen checkboxes.',
    },
    rated,
  ];
});
</script>

<template>
  <Card class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="fact in facts"
        :key="fact.title"
        class="flex items-start space-x-3"
      >
        <Component
          :is="fact.icon"
          class="mt-1 size-8 shrink-0 text-primary-dark"
        />

        <div class="flex flex-col">
          <h2
            class="font-semibold"
            v-text="fact.title"
          />

          <p
            class="text-sm text-grey-dark"
            v-text="fact.description"
          />
        </div>
      </div>
    </div>
  </Card>
</template>
