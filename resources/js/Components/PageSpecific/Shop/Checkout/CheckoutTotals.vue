<script setup lang="ts">
import { computed, nextTick } from 'vue';
import CheckoutDiscountCode from '@/Components/PageSpecific/Shop/Checkout/CheckoutDiscountCode.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid';
import { router, usePage } from '@inertiajs/vue3';
import eventBus from '@/eventBus';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { ShopHolidayProps } from '@/types/Shop';
import UkShippingOnlyModal from '@/Components/PageSpecific/Shop/Checkout/UkShippingOnlyModal.vue';

defineProps<{
  subtotal: string;
  postage: string;
  discount?: string;
  totalFees: string;
  total: string;
  digitalOnly: boolean;
}>();

const shopHoliday = computed(
  (): ShopHolidayProps | undefined =>
    usePage<{ shopHoliday?: ShopHolidayProps }>().props.shopHoliday,
);

const removeDiscountCode = () => {
  router.delete('/shop/basket/discount', {
    preserveScroll: true,
    onFinish: () => {
      void nextTick(() => {
        useJourneyTracking().logEvent(
          'clicked',
          'Checkout/Totals/RemovedDiscountCode',
        );

        eventBus.$emit('refresh-payment-element');
      });
    },
  });
};
</script>

<template>
  <div
    class="mt-4 w-full border-primary-light/60 pt-4"
    :class="{
      'border-t':
        digitalOnly === false || (digitalOnly === true && subtotal !== total),
    }"
  >
    <dl class="space-y-3">
      <div
        v-if="
          digitalOnly === false || (digitalOnly === true && subtotal !== total)
        "
        class="flex justify-between gap-3"
      >
        <dt class="text-grey-dark">Subtotal</dt>
        <dd
          class="font-semibold"
          v-text="subtotal"
        />
      </div>

      <div
        v-if="!digitalOnly"
        class="flex justify-between gap-3"
      >
        <dt class="flex items-center space-x-1 text-grey-dark">
          <span>Postage to the United Kingdom</span>

          <UkShippingOnlyModal />
        </dt>
        <dd
          class="font-semibold"
          v-text="postage"
        />
      </div>

      <div
        v-if="discount"
        class="flex justify-between gap-3"
      >
        <dt class="flex items-center gap-2 text-grey-dark">
          <span>Discount</span>

          <button
            type="button"
            aria-label="Remove discount code"
            class="flex size-6 shrink-0 cursor-pointer items-center justify-center rounded-full transition-colors hover:bg-primary-light/40"
            @click="removeDiscountCode()"
          >
            <XMarkIcon class="size-4" />
          </button>
        </dt>
        <dd
          class="font-semibold text-primary-dark"
          v-text="`-${discount}`"
        />
      </div>

      <div
        class="flex justify-between gap-3 border-t border-secondary pt-3 text-xl font-semibold sm:text-2xl"
      >
        <dt>Total</dt>
        <dd v-text="total" />
      </div>
    </dl>

    <div
      v-if="shopHoliday"
      class="mt-4 flex gap-3 rounded-md border border-l-8 border-secondary bg-secondary/50 p-3 shadow-sm"
    >
      <ExclamationCircleIcon class="size-6 shrink-0" />

      <div class="space-y-1">
        <p class="font-semibold">Please note</p>

        <p
          class="text-sm leading-relaxed"
          v-text="shopHoliday.notice"
        />
      </div>
    </div>

    <div
      v-if="digitalOnly === false"
      class="relative mt-4 flex flex-col gap-2"
    >
      <p class="text-sm text-grey-dark">
        Dispatched within 1 - 2 working days by first class post, usually
        arriving within 1 - 2 days.
      </p>
    </div>

    <div
      v-if="!discount"
      class="mt-4"
    >
      <CheckoutDiscountCode />
    </div>
  </div>
</template>
