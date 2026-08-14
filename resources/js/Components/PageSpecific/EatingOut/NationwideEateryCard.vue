<script lang="ts" setup>
import { NationwideEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ref, useTemplateRef } from 'vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { StarIcon, FlagIcon, ArrowRightIcon } from '@heroicons/vue/24/solid';
import { pluralise } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import ReportEateryModal from '@/Components/PageSpecific/EatingOut/Details/Modals/ReportEateryModal.vue';

const props = defineProps<{ eatery: NationwideEatery }>();

const venueSummary = computed(() =>
  [
    props.eatery.venue_type,
    props.eatery.cuisine && props.eatery.cuisine !== 'English'
      ? props.eatery.cuisine
      : null,
  ]
    .filter(Boolean)
    .join(' · '),
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'NationwideEateryCard',
  {
    eateryId: props.eatery.key,
  },
);

const icon = computed((): string => {
  if (props.eatery.type === 'Hotel / B&B') {
    return 'hotel';
  }

  if (props.eatery.type === 'Attraction') {
    return 'attraction';
  }

  return 'eatery';
});

const showReportPlaceModal = ref(false);
</script>

<template>
  <Card
    ref="card"
    class="overflow-hidden"
  >
    <div class="flex flex-1 flex-col space-y-3">
      <div class="flex items-start justify-between gap-3">
        <div class="flex flex-1 items-center gap-2">
          <Icon
            :name="icon"
            class="h-7 w-7 shrink-0 text-primary md:h-8 md:w-8"
          />

          <h2
            class="flex-1 text-xl font-semibold text-primary-dark transition hover:text-grey-dark md:text-2xl"
          >
            <Link
              :href="eatery.link"
              prefetch
              :on-before="
                () =>
                  useJourneyTracking().logEvent(
                    'clicked',
                    'NationwideEateryCard/TitleLink',
                    { eateryId: eatery.key },
                  )
              "
            >
              {{ eatery.name }}
            </Link>
          </h2>
        </div>

        <div
          v-if="eatery.reviews.number > 0"
          class="flex shrink-0 flex-col items-end space-y-1"
        >
          <StarRating
            :rating="eatery.reviews.average"
            show-all
            size="w-4 h-4 md:w-5 md:h-5"
          />

          <span class="text-xs text-grey-dark md:text-sm">
            from
            <strong>
              {{ eatery.reviews.number }}
              {{ pluralise('review', eatery.reviews.number) }}
            </strong>
          </span>
        </div>
      </div>

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

      <div class="flex flex-1 flex-col space-y-2 md:space-y-4">
        <p
          class="prose max-w-none md:prose-lg"
          v-html="eatery.info"
        />
      </div>
    </div>

    <div
      class="-mx-4 mt-4 -mb-4 flex items-start justify-between gap-3 bg-primary-lightest/60 p-4 lg:items-end"
    >
      <CoeliacButton
        label="More Details"
        :href="eatery.link"
        size="md"
        bold
        :icon="ArrowRightIcon"
        icon-position="right"
        prefetch
        :on-before="
          () =>
            useJourneyTracking().logEvent(
              'clicked',
              'NationwideEateryCard/CtaButton',
              { eateryId: eatery.key },
            )
        "
      />

      <div
        class="flex flex-col items-start gap-2 lg:flex-row lg:items-center lg:gap-4"
      >
        <Link
          class="flex cursor-pointer items-center space-x-2 text-xs font-semibold text-grey transition-all ease-in-out hover:text-black sm:text-sm"
          :href="`${eatery.link}#leave-review`"
        >
          <StarIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
          <span>Review</span>
        </Link>

        <a
          class="flex cursor-pointer items-center space-x-2 text-xs font-semibold text-grey transition-all ease-in-out hover:text-black sm:text-sm"
          @click.prevent="showReportPlaceModal = true"
        >
          <FlagIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
          <span>Report</span>
        </a>
      </div>
    </div>
  </Card>

  <ReportEateryModal
    :eatery-name="eatery.name"
    :eatery-id="eatery.key"
    :is-nationwide="false"
    :show="showReportPlaceModal"
    @close="showReportPlaceModal = false"
  />
</template>
