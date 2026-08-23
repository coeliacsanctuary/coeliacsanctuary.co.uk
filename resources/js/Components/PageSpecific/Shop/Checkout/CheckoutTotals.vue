<script setup lang="ts">
import { nextTick } from 'vue';
import CheckoutDiscountCode from '@/Components/PageSpecific/Shop/Checkout/CheckoutDiscountCode.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import eventBus from '@/eventBus';
import useJourneyTracking from '@/composables/useJourneyTracking';

defineProps<{
  subtotal: string;
  postage: string;
  discount?: string;
  fees: { fee: string; description?: string }[];
  totalFees: string;
  total: string;
}>();

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
  <div class="mt-4 w-full border-t border-primary-light/60 pt-4">
    <dl class="space-y-3">
      <div class="flex justify-between gap-3">
        <dt class="text-grey-dark">Subtotal</dt>
        <dd
          class="font-semibold"
          v-text="subtotal"
        />
      </div>

      <div class="flex justify-between gap-3">
        <dt class="text-grey-dark">Postage</dt>
        <dd
          class="font-semibold"
          v-text="postage"
        />
      </div>

      <template v-if="fees.length > 0">
        <div
          v-for="(fee, x) in fees"
          :key="x"
          class="flex justify-between gap-3"
        >
          <dt
            class="text-grey-dark"
            v-text="fee.description ? fee.description : 'Customs Charge'"
          />
          <dd
            class="font-semibold"
            v-text="fee.fee"
          />
        </div>

        <div
          v-if="fees.length > 1"
          class="flex justify-between gap-3"
        >
          <dt class="text-grey-dark">Total Fees</dt>
          <dd
            class="font-semibold"
            v-text="totalFees"
          />
        </div>
      </template>

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
      v-if="!discount"
      class="mt-4"
    >
      <CheckoutDiscountCode />
    </div>
  </div>
</template>
