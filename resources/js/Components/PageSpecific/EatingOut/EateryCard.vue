<script lang="ts" setup>
import { TownEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ref, useTemplateRef } from 'vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { StarIcon, FlagIcon, ArrowRightIcon } from '@heroicons/vue/24/solid';
import { pluralise } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import EateryRestaurants from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryRestaurants.vue';
import ReportEateryModal from '@/Components/PageSpecific/EatingOut/Details/Modals/ReportEateryModal.vue';

const props = defineProps<{ eatery: TownEatery }>();

const hasPhysicalLocation = computed(() => {
  if (props.eatery.isNationwideBranch) {
    return true;
  }

  return props.eatery.county.id !== 1;
});

const eateryName = computed(() => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
});

const eateryLink = computed(() => {
  if (props.eatery.branch) {
    return props.eatery.branch.link;
  }

  return props.eatery.link;
});

const location = computed(() =>
  props.eatery.branch ? props.eatery.branch.location : props.eatery.location,
);

const venueSummary = computed(() =>
  [
    props.eatery.isNationwideBranch ? 'Nationwide Chain' : null,
    props.eatery.venue_type,
    props.eatery.cuisine && props.eatery.cuisine !== 'English'
      ? props.eatery.cuisine
      : null,
  ]
    .filter(Boolean)
    .join(' · '),
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('eateryCard'),
  'scrolled_into_view',
  'EateryCard',
  {
    eateryId: props.eatery.id,
    branchId: props.eatery.branch?.id,
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
    ref="eateryCard"
    class="overflow-hidden"
  >
    <div class="flex items-stretch gap-4">
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
                :href="eateryLink"
                prefetch
                :on-before="
                  () =>
                    useJourneyTracking().logEvent(
                      'clicked',
                      'EateryCard/TitleLink',
                      { eateryId: eatery.id },
                    )
                "
              >
                {{ eateryName }}
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

        <div
          v-if="hasPhysicalLocation || eatery.distance"
          class="flex flex-col space-y-1"
        >
          <span
            v-if="hasPhysicalLocation"
            class="font-semibold text-grey-darkest md:text-lg"
            v-html="location.address"
          />

          <span
            v-if="eatery.distance"
            class="text-sm font-semibold text-grey-darker"
          >
            Around {{ eatery.distance.toFixed(2) }} miles away...
          </span>
        </div>

        <div class="flex flex-1 flex-col space-y-2 md:space-y-4">
          <EateryRestaurants
            v-if="eatery.restaurants.length"
            :restaurants="eatery.restaurants"
          />

          <p
            v-else
            class="prose max-w-none md:prose-lg"
            v-html="eatery.info"
          />
        </div>

        <span class="text-xs text-grey-dark italic">
          Last updated {{ eatery.last_updated_human }}
        </span>
      </div>

      <div
        v-if="hasPhysicalLocation"
        class="-mt-4 -mr-4 -mb-4 hidden shrink-0 sm:block sm:w-2/5 lg:w-1/3 [&>div]:mb-0"
      >
        <StaticMap
          map-classes="h-full max-h-[37.5rem]"
          :lat="location.lat"
          :lng="location.lng"
          :title="`${eatery.branch && eatery.branch.name ? eatery.branch.name + ' - ' : ''} ${eatery.name} - ${location.address}`"
        />
      </div>
    </div>

    <div
      class="-mx-4 mt-4 -mb-4 flex items-start justify-between gap-3 bg-primary-lightest/60 p-4 sm:items-end"
    >
      <CoeliacButton
        label="More Details"
        :href="eateryLink"
        size="md"
        bold
        :icon="ArrowRightIcon"
        icon-position="right"
        prefetch
        :on-before="
          () =>
            useJourneyTracking().logEvent('clicked', 'EateryCard/CtaButto', {
              eateryId: eatery.id,
            })
        "
      />

      <div
        class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-4"
      >
        <Link
          class="flex cursor-pointer items-center space-x-2 text-xs font-semibold text-grey transition-all ease-in-out hover:text-black sm:text-sm"
          :href="`${eateryLink}#leave-review`"
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
    :eatery-name="eateryName"
    :eatery-id="eatery.id"
    :branch-id="eatery.branch?.id"
    :is-nationwide="!!eatery.isNationwideBranch"
    :show="showReportPlaceModal"
    @close="showReportPlaceModal = false"
  />
</template>
