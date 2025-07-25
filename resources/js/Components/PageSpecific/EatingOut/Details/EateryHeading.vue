<script lang="ts" setup>
import {
  DetailedEatery,
  StarRating as StarRatingType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';
import EateryHeaderLinks from '@/Components/PageSpecific/EatingOut/Details/EateryHeaderLinks.vue';
import Icon from '@/Components/Icon.vue';
import Heading from '@/Components/Heading.vue';

const props = defineProps<{
  eatery: DetailedEatery;
  previous: string;
  name: string;
}>();

const iconName = computed((): string => {
  if (props.eatery.type === 'Hotel / B&B') {
    return 'hotel';
  }

  if (props.eatery.type === 'Attraction') {
    return 'attraction';
  }

  return 'eatery';
});

const averageRating = (): StarRatingType =>
  parseFloat(props.eatery.reviews.average) as StarRatingType;

const eateryName = (): string => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};
</script>

<template>
  <Card class="space-y-2 lg:rounded-b-lg lg:p-8">
    <Heading
      :border="false"
      :back-link="{
        href: props.previous,
        label: props.name,
        position: 'top',
        direction: 'left',
      }"
    >
      <div class="flex items-center">
        <div class="mr-4 w-10 text-primary">
          <Icon
            :name="iconName"
            class="h-10 w-10"
          />
        </div>

        <span
          class="text-left"
          v-text="eateryName()"
        />
      </div>
    </Heading>

    <div
      v-if="eatery.reviews.user_reviews?.length > 0"
      class="flex items-center justify-between gap-2 sm:flex-row-reverse"
    >
      <span class="flex-1">
        Rated <strong>{{ eatery.reviews.average }} stars</strong> from
        <strong
          >{{ eatery.reviews.user_reviews.length }} review{{
            eatery.reviews.user_reviews.length > 1 ? 's' : ''
          }}</strong
        >
      </span>

      <StarRating
        :rating="averageRating()"
        show-all
      />
    </div>

    <div>
      <div class="flex flex-col text-sm font-semibold text-grey-darker">
        <a
          v-if="eatery.county.id === 1"
          class="hover:text-black"
          href="/wheretoeat/nationwide"
        >
          Nationwide Chain
        </a>
        <div>
          <span
            v-if="eatery.venue_type"
            v-text="eatery.venue_type"
          />
          <span
            v-if="eatery.cuisine"
            v-text="`, ${eatery.cuisine}`"
          />
        </div>
      </div>

      <div
        v-if="eatery.town.name !== 'Nationwide'"
        class="1 flex space-x-1 text-xs font-semibold text-grey-darker"
      >
        <Link
          v-if="eatery.area"
          :href="eatery.area.link"
        >
          {{ eatery.area.name }},
        </Link>
        <Link :href="eatery.town.link"> {{ eatery.town.name }}, </Link>
        <Link :href="eatery.county.link">
          {{ eatery.county.name }}
        </Link>
      </div>

      <div
        v-if="eatery.branch"
        class="2 flex space-x-1 text-xs font-semibold text-grey-darker"
      >
        <Link :href="eatery.branch.town.link">
          {{ eatery.branch.town.name }},
        </Link>
        <Link :href="eatery.branch.county.link">
          {{ eatery.branch.county.name }}
        </Link>
      </div>
    </div>

    <EateryHeaderLinks :eatery="eatery" />
  </Card>
</template>
