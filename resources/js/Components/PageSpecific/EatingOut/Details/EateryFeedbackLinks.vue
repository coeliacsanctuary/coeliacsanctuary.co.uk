<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
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
  <Card class="flex flex-col space-y-3">
    <SubHeading
      text-size="small"
      class="pb-2"
    >
      Help us improve {{ eateryName() }}
    </SubHeading>

    <ul class="flex flex-wrap gap-2 xmd:flex-col">
      <li
        class="flex-1 rounded-sm bg-secondary/25 px-2 py-1.5 leading-none transition hover:bg-secondary/75 xmd:flex-none xmd:px-3 xmd:py-2"
      >
        <a
          class="flex cursor-pointer items-center justify-center gap-2 text-xs font-semibold text-grey transition ease-in-out hover:text-black xmd:justify-start xmd:gap-3 xmd:text-sm"
          @click.prevent="$emit('goToReview')"
        >
          <StarIcon class="size-3.5 shrink-0 xmd:size-4" />
          <span>Write a review</span>
        </a>
      </li>

      <li
        class="flex-1 rounded-sm bg-secondary/25 px-2 py-1.5 leading-none transition hover:bg-secondary/75 xmd:flex-none xmd:px-3 xmd:py-2"
      >
        <a
          class="flex cursor-pointer items-center justify-center gap-2 text-xs font-semibold text-grey transition ease-in-out hover:text-black xmd:justify-start xmd:gap-3 xmd:text-sm"
          @click.prevent="showEditModal = true"
        >
          <PencilIcon class="size-3.5 shrink-0 xmd:size-4" />
          <span>Suggest an edit</span>
        </a>
      </li>

      <li
        class="flex-1 rounded-sm bg-secondary/25 px-2 py-1.5 leading-none transition hover:bg-secondary/75 xmd:flex-none xmd:px-3 xmd:py-2"
      >
        <a
          class="flex cursor-pointer items-center justify-center gap-2 text-xs font-semibold text-grey transition ease-in-out hover:text-black xmd:justify-start xmd:gap-3 xmd:text-sm"
          @click.prevent="showReportPlaceModal = true"
        >
          <FlagIcon class="size-3.5 shrink-0 xmd:size-4" />
          <span>Report a problem</span>
        </a>
      </li>
    </ul>

    <EaterySuggestEditsModal
      :eatery-name="eateryName()"
      :eatery-id="eatery.id"
      :show="showEditModal"
      :is-nationwide="eatery.is_nationwide && !eatery.branch"
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
      :is-nationwide="eatery.is_nationwide"
      :show="showReportPlaceModal"
      @close="showReportPlaceModal = false"
    />
  </Card>
</template>
