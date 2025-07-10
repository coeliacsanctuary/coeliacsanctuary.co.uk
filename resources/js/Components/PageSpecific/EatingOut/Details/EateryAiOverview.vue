<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { onMounted, ref } from 'vue';
import Loader from '@/Components/Loader.vue';
import SealiacSeal from '@/Svg/SealiacSeal.vue';
import axios, { AxiosResponse } from 'axios';
import { HandThumbDownIcon, HandThumbUpIcon } from '@heroicons/vue/24/solid';
import SubHeading from '@/Components/SubHeading.vue';
import Modal from '@/Components/Overlays/Modal.vue';

const props = defineProps<{
  eateryName: string;
  eateryId: number;
  branchId?: number;
}>();

const emit = defineEmits(['onError']);

const isLoading = ref(true);
const overview = ref<string | undefined>();

const showWhatsThisModal = ref(false);
const isSubmittingRating = ref(false);
const hasSubmittedRating = ref(false);

const getEndpoint = (): string => {
  let url = `/api/wheretoeat/${props.eateryId}/sealiac`;

  if (props.branchId) {
    url += `?branchId=${props.branchId}`;
  }

  return url;
};

const getAiOverview = () => {
  axios
    .get(getEndpoint())
    .then((response: AxiosResponse<{ data: string }>) => {
      overview.value = response.data.data;
      isLoading.value = false;
    })
    .catch(() => {
      emit('onError');
    });
};

const submitRating = (rating: 'up' | 'down') => {
  isSubmittingRating.value = true;

  axios
    .post(getEndpoint(), { rating })
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
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <template v-if="isLoading">
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
        <SealiacSeal
          class="mr-1 mb-2 size-12 flex-shrink-0 xmd:size-16 sm:max-xmd:size-14 md:mr-2 lg:mr-3"
        />

        <SubHeading>
          Here's what Sealiac the Seal thinks about {{ eateryName }}
        </SubHeading>
      </div>

      <div class="flex overflow-hidden">
        <div
          class="prose flex max-w-none flex-col md:prose-lg xl:prose-xl"
          v-html="overview"
        />
      </div>

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
        Sealiac the Seal is the Coeliac Sanctuary mascot, the text overview of
        {{ eateryName }} was generated using AI by analysing the information we
        hold for {{ eateryName }}, and using reviews submitted through our
        website.
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
}

.quote-elem.close {
  height: 100%;
  float: right;
  shape-outside: inset(calc(100% - 4rem) 0 0);
  object-fit: contain;
  object-position: bottom;
  display: flex;
  align-items: flex-end;
}

.quote-elem span {
  height: 4rem;
  display: inline-block;
}
</style>
