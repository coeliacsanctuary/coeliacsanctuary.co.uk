<script lang="ts" setup>
import {
  DetailedEatery,
  StarRating as StarRatingType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, Ref, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';
import Icon from '@/Components/Icon.vue';
import Heading from '@/Components/Heading.vue';
import ReviewImageModal from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageModal.vue';
import { pluralise } from '@/helpers';

const props = defineProps<{
  eatery: DetailedEatery;
  previous: string;
  name: string;
}>();

const displayImage: Ref<false | number> = ref(false);

const icon = computed((): string => {
  if (props.eatery.type === 'Hotel / B&B') {
    return 'hotel';
  }

  if (props.eatery.type === 'Attraction') {
    return 'attraction';
  }

  return 'eatery';
});

const eateryName = computed(() => {
  if (
    props.eatery.branch &&
    props.eatery.branch.name &&
    props.eatery.name !== props.eatery.branch.name
  ) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
});

const averageRating = computed(
  () => parseFloat(props.eatery.reviews.average) as StarRatingType,
);

const venueSummary = computed(() =>
  [
    props.eatery.county.id === 1 ? 'Nationwide Chain' : null,
    props.eatery.venue_type,
    props.eatery.cuisine && props.eatery.cuisine !== 'English'
      ? props.eatery.cuisine
      : null,
  ]
    .filter(Boolean)
    .join(' · '),
);

/** Thumbnails shown before the rest collapse into a +N tile on the last one. */
const stripSize = 4;

const images = computed(() => props.eatery.reviews.images ?? []);

const strip = computed(() => images.value.slice(0, stripSize));

const remainingImages = computed(
  () => images.value.length - strip.value.length,
);
</script>

<template>
  <Card class="flex flex-col space-y-3">
    <Heading
      :border="false"
      classes="text-left"
      :back-link="{
        href: previous,
        label: name,
        position: 'top',
        direction: 'left',
      }"
    >
      <span class="flex items-center gap-3">
        <Icon
          :name="icon"
          class="size-8 shrink-0 text-primary md:size-10"
        />

        <span v-text="eateryName" />
      </span>
    </Heading>

    <div
      v-if="eatery.is_fully_gf || venueSummary"
      class="flex flex-wrap items-center gap-2"
    >
      <span
        v-if="eatery.is_fully_gf"
        class="rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-center text-sm font-semibold"
      >
        100% Gluten Free
      </span>

      <span
        v-if="venueSummary"
        class="text-sm font-semibold text-grey-darker md:text-base"
        v-text="venueSummary"
      />
    </div>

    <div
      v-if="eatery.town.name !== 'Nationwide'"
      class="flex flex-wrap gap-x-1 text-sm font-semibold text-grey-darker"
    >
      <Link
        v-if="eatery.area"
        class="transition hover:text-black"
        :href="eatery.area.link"
      >
        {{ eatery.area.name }},
      </Link>

      <Link
        class="transition hover:text-black"
        :href="eatery.town.link"
      >
        {{ eatery.town.name }},
      </Link>

      <Link
        class="transition hover:text-black"
        :href="eatery.county.link"
      >
        {{ eatery.county.name }}
      </Link>
    </div>

    <div
      v-if="eatery.branch"
      class="flex flex-wrap gap-x-1 text-sm font-semibold text-grey-darker"
    >
      <Link
        class="transition hover:text-black"
        :href="eatery.branch.town.link"
      >
        {{ eatery.branch.town.name }},
      </Link>

      <Link
        class="transition hover:text-black"
        :href="eatery.branch.county.link"
      >
        {{ eatery.branch.county.name }}
      </Link>
    </div>

    <div
      v-if="eatery.reviews.number > 0"
      class="flex items-center gap-2 xmd:hidden"
    >
      <StarRating
        :rating="averageRating"
        show-all
        size="w-5 h-5"
      />

      <span class="text-sm text-grey-dark">
        <strong>{{ eatery.reviews.average }}</strong> from
        <strong>
          {{ eatery.reviews.number }}
          {{ pluralise('review', eatery.reviews.number) }}
        </strong>
      </span>
    </div>

    <ul
      v-if="strip.length > 0"
      class="flex flex-wrap gap-2"
    >
      <li
        v-for="(image, index) in strip"
        :key="image.id"
        class="group relative size-16 cursor-pointer overflow-hidden rounded-sm md:size-20"
        @click="displayImage = index"
      >
        <img
          class="h-full w-full object-cover transition group-hover:scale-105"
          :src="image.thumbnail"
          :alt="`Photo of ${eateryName}`"
          loading="lazy"
        />

        <div
          v-if="index === strip.length - 1 && remainingImages > 0"
          class="absolute inset-0 flex items-center justify-center bg-black/50 text-sm font-semibold text-white"
        >
          +{{ remainingImages }}
        </div>
      </li>
    </ul>

    <ReviewImageModal
      v-model="displayImage"
      :images="images"
      :eatery-name="eateryName"
    />
  </Card>
</template>
