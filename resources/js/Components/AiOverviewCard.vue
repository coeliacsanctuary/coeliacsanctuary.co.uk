<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { computed, onMounted, ref, useSlots } from 'vue';
import Loader from '@/Components/Loader.vue';
import SealiacSeal from '@/Svg/SealiacSeal.vue';
import axios, { AxiosResponse } from 'axios';
import {
  ChevronDownIcon,
  HandThumbDownIcon,
  HandThumbUpIcon,
} from '@heroicons/vue/24/solid';
import SubHeading from '@/Components/SubHeading.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import { DataResponse } from '@/types/GenericTypes';

type OverviewResponse = {
  overview: string;
  id: number;
};

const props = withDefaults(
  defineProps<{
    endpoint: string;
    collapsible?: boolean;
    /** Sizes the card to sit alongside other content rather than lead the page. */
    compact?: boolean;
  }>(),
  { collapsible: false, compact: false },
);

const emit = defineEmits(['onError']);

const slots = useSlots();

const isLoading = ref(true);
const overview = ref<OverviewResponse | undefined>();

const showWhatsThisModal = ref(false);
const isSubmittingRating = ref(false);
const hasSubmittedRating = ref(false);
const isExpanded = ref(false);

/**
 * The overview is only ever populated from the browser, so parsing it here is
 * safe. Counting the top level blocks tells us whether collapsing to the first
 * one actually hides anything worth offering a toggle for.
 */
const blockCount = computed(() => {
  if (!overview.value) {
    return 0;
  }

  return new DOMParser().parseFromString(overview.value.overview, 'text/html')
    .body.children.length;
});

const canExpand = computed(() => props.collapsible && blockCount.value > 1);

const isCollapsed = computed(() => canExpand.value && !isExpanded.value);

const sealClasses = computed(() =>
  props.compact
    ? 'mr-3 mb-2 size-10 flex-shrink-0 md:size-12'
    : 'mr-3 mb-2 size-12 flex-shrink-0 sm:max-xmd:size-14 md:mr-2 xmd:size-16 lg:mr-3',
);

const titleClasses = computed(() =>
  props.compact
    ? 'prose mt-4 max-w-none font-semibold md:prose-lg'
    : 'prose prose-lg mt-4 max-w-none font-semibold md:prose-xl xl:prose-2xl',
);

const bodyClasses = computed(() =>
  props.compact
    ? 'prose max-w-none md:prose-lg'
    : 'prose max-w-none md:prose-lg xl:prose-xl',
);

const getAiOverview = () => {
  axios
    .get(props.endpoint)
    .then((response: AxiosResponse<DataResponse<OverviewResponse>>) => {
      overview.value = response.data.data;
      isLoading.value = false;
    })
    .catch(() => {
      emit('onError');
    });
};

const submitRating = (rating: 'up' | 'down') => {
  if (isLoading.value || !overview.value) {
    return;
  }

  isSubmittingRating.value = true;

  axios
    .post(`/api/sealiac-overview-feedback/${overview.value.id}`, { rating })
    .then(() => {
      //
    })
    .catch(() => {
      //
    })
    .finally(() => {
      hasSubmittedRating.value = true;
    });
};

onMounted(() => {
  getAiOverview();
});
</script>

<template>
  <Card v-bind="$attrs">
    <template v-if="isLoading || !overview">
      <Loader
        color="primary"
        :absolute="false"
        size="size-18"
        width="border-6"
        display
      />
    </template>
    <template v-else>
      <div class="flex w-full items-center border-b border-primary-light">
        <SealiacSeal :class="sealClasses" />

        <SubHeading :text-size="compact ? 'base' : 'xl'">
          Sealiac Says...
        </SubHeading>
      </div>
      <div
        v-if="slots.title"
        :class="titleClasses"
      >
        <slot name="title" />
      </div>

      <div class="mt-4 flex overflow-hidden">
        <div
          :class="[
            bodyClasses,
            { '[&>*:not(:first-child)]:hidden': isCollapsed },
          ]"
          v-html="overview.overview"
        />
      </div>

      <CoeliacButton
        v-if="canExpand"
        as="button"
        type="button"
        theme="secondary"
        size="lg"
        bold
        classes="mt-4 mb-4 self-start"
        :label="isExpanded ? 'Read less' : 'Read more'"
        :icon="ChevronDownIcon"
        icon-position="right"
        :icon-classes="isExpanded ? 'rotate-180 transition' : 'transition'"
        @click="isExpanded = !isExpanded"
      />

      <div
        class="flex justify-between border-t border-primary-light pt-4 text-sm md:text-base"
      >
        <div
          class="cursor-pointer font-semibold text-primary-dark/80 transition hover:text-black/50"
          @click="showWhatsThisModal = true"
        >
          Whats this?
        </div>

        <div class="relative flex items-center space-x-1">
          <transition
            enter-active-class="duration-300 ease-out"
            enter-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in"
            leave-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <span
              v-if="hasSubmittedRating"
              class="font-semibold text-green-dark"
            >
              Thank you for submitting your feedback
            </span>
          </transition>

          <template v-if="!hasSubmittedRating">
            <Loader
              :display="isSubmittingRating"
              on-top
              blur
              fade
              color="dark"
            />

            <span class="font-semibold text-black/50">
              Rate this overview
            </span>

            <HandThumbUpIcon
              class="size-5 cursor-pointer text-primary-dark/80 transition hover:text-black/50 md:size-6"
              @click="submitRating('up')"
            />
            <HandThumbDownIcon
              class="size-5 cursor-pointer text-primary-dark/80 transition hover:text-black/50 md:size-6"
              @click="submitRating('down')"
            />
          </template>
        </div>
      </div>
    </template>
  </Card>

  <Modal
    :open="showWhatsThisModal"
    size="small"
    @close="showWhatsThisModal = false"
  >
    <template #header>
      <h3 class="pl-3 font-semibold lg:text-lg">
        What is Sealiac the Seal's overview
      </h3>
    </template>

    <div class="flex flex-col space-y-2 p-2">
      <p class="prose lg:prose-lg">
        <template v-if="slots.helpIntro">
          <slot name="helpIntro" />
        </template>
        <template v-else>
          Sealiac the Seal is the Coeliac Sanctuary mascot, this text overview
          was generated using AI by analysing the information we hold on our
          website, and using reviews submitted through our website.
        </template>
      </p>
      <p class="prose lg:prose-lg">
        This AI overview will be updated automatically when a new review is
        submitted and approved.
      </p>
      <p class="prose lg:prose-lg">
        You can rate the generated overview by using the thumbs up and down
        icons below Sealiac the Seals thoughts.
      </p>
    </div>
  </Modal>
</template>

<style>
.quote-elem {
  margin: 0;
  height: 4rem;
  width: 4rem;
  padding: 0;
  font-family: var(--font-serif);
  font-size: 10rem;
  line-height: 0.9;
  color: rgba(35, 124, 189, 0.2);
}

.quote-elem.open {
  float: left;
  margin-right: 5px;
}

.quote-elem.close {
  height: 100%;
  float: right;
  shape-outside: inset(calc(100% - 4rem) 0 0);
  object-fit: contain;
  object-position: bottom;
  display: flex;
  align-items: flex-end;
  margin-left: 5px;
}

.quote-elem span {
  height: 4rem;
  display: inline-block;
}
</style>
