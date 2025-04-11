<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { StarIcon, PencilIcon, FlagIcon } from '@heroicons/vue/24/solid';
import { DetailedEatery } from '@/types/EateryTypes';
import EaterySuggestEditsModal from '@/Components/PageSpecific/EatingOut/Details/Modals/EaterySuggestEditsModal.vue';
import { ref } from 'vue';
import ReportEateryModal from '@/Components/PageSpecific/EatingOut/Details/Modals/ReportEateryModal.vue';

const props = defineProps<{ eatery: DetailedEatery }>();

defineEmits(['goToReview']);

const showEditModal = ref(false);
const showReportPlaceModal = ref(false);

const eateryName = (): string => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <h3 class="text-lg font-semibold lg:text-xl">
      Help us improve {{ eateryName() }}
    </h3>

    <ul class="flex flex-wrap items-center gap-2 lg:gap-4">
      <li
        class="rounded-sm bg-secondary/25 px-2 py-1 leading-none transition-all hover:bg-secondary/75 lg:px-3 lg:py-2"
      >
        <a
          class="flex cursor-pointer items-center space-x-2 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black lg:space-x-3 lg:text-lg"
          @click.prevent="$emit('goToReview')"
        >
          <StarIcon class="h-4 w-4 lg:h-6 lg:w-6" />
          <span>Review</span>
        </a>
      </li>

      <li
        class="rounded-sm bg-secondary/25 px-2 py-1 leading-none transition-all hover:bg-secondary/75 lg:px-3 lg:py-2"
      >
        <a
          class="flex cursor-pointer items-center space-x-2 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black lg:space-x-3 lg:text-lg"
          @click.prevent="showEditModal = true"
        >
          <PencilIcon class="h-4 w-4 lg:h-6 lg:w-6" />
          <span>Edit</span>
        </a>
      </li>

      <li
        class="rounded-sm bg-secondary/25 px-2 py-1 leading-none transition-all hover:bg-secondary/75 lg:px-3 lg:py-2"
      >
        <a
          class="flex cursor-pointer items-center space-x-2 text-sm font-semibold text-grey transition-all ease-in-out hover:text-black lg:space-x-3 lg:text-lg"
          @click.prevent="showReportPlaceModal = true"
        >
          <FlagIcon class="h-4 w-4 lg:h-6 lg:w-6" />
          <span>Report</span>
        </a>
      </li>
    </ul>
  </Card>

  <EaterySuggestEditsModal
    :eatery-name="eateryName()"
    :eatery-id="eatery.id"
    :show="showEditModal"
    @close="showEditModal = false"
    @open-report="
      showEditModal = false;
      showReportPlaceModal = true;
    "
  />

  <ReportEateryModal
    :eatery-name="eateryName()"
    :eatery-id="eatery.id"
    :branch-id="eatery.branch?.id"
    :show="showReportPlaceModal"
    @close="showReportPlaceModal = false"
  />
</template>
