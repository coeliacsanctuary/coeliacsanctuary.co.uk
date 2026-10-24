<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { TravelCardReviewSummary } from '@/types/Shop';
import {
  BoltIcon,
  DevicePhoneMobileIcon,
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
  digitalCardsEnabled: boolean;
}>();

type Fact = {
  icon: FunctionalComponent;
  title: string;
  description: string;
};

type FactGroup = {
  title: string;
  facts: Fact[];
};

const factGroups = computed((): FactGroup[] => {
  const rated: Fact = props.reviews.average
    ? {
        icon: StarIcon,
        title: `Rated ${props.reviews.average} out of 5`,
        description: `From ${props.reviews.count} verified ${pluralise('review', props.reviews.count)} left by customers after their order was completed.`,
      }
    : {
        icon: StarIcon,
        title: 'Sold worldwide',
        description:
          "I've sent these all over the world, and every customer is invited to review their order once it's on its way.",
      };

  if (!props.digitalCardsEnabled) {
    return [
      {
        title: '',
        facts: [
          {
            icon: LanguageIcon,
            title: 'Professionally translated',
            description:
              'Every card, is translated by a native speaker, never by Google Translate or AI, so what it says is accurate and easy to follow.',
          },
          rated,
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
        ],
      },
    ];
  }

  return [
    {
      title: 'Every card',
      facts: [
        {
          icon: LanguageIcon,
          title: 'Professionally translated',
          description:
            'Every card, physical or digital, is translated by a native speaker, never by Google Translate or AI, so what it says is accurate and easy to follow.',
        },
        rated,
      ],
    },
    {
      title: 'My physical cards',
      facts: [
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
      ],
    },
    {
      title: 'My digital cards',
      facts: [
        {
          icon: BoltIcon,
          title: 'Ready in minutes',
          description:
            "No waiting on the post. Buy a digital card and it's yours straight away, which matters if you're flying tomorrow.",
        },
        {
          icon: DevicePhoneMobileIcon,
          title: 'Always in your pocket',
          description:
            "View it in your browser or add it to Apple or Google Wallet, so it's with you even when the physical card is back at the hotel.",
        },
      ],
    },
  ];
});
</script>

<template>
  <Card class="space-y-8">
    <div
      v-for="group in factGroups"
      :key="group.title"
      class="space-y-4"
    >
      <SubHeading
        as="h2"
        text-size="xs"
      >
        {{ group.title }}
      </SubHeading>

      <div class="grid gap-6 sm:grid-cols-2">
        <div
          v-for="fact in group.facts"
          :key="fact.title"
          class="flex items-start space-x-3"
        >
          <Component
            :is="fact.icon"
            class="mt-1 size-8 shrink-0 text-primary-dark"
          />

          <div class="flex flex-col">
            <h3
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
    </div>
  </Card>
</template>
