<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { useForm } from 'laravel-precognition-vue-inertia';
import { nextTick } from 'vue';
import eventBus from '@/eventBus';
import { InertiaForm } from '@/types/Core';
import useGoogleEvents from '@/composables/useGoogleEvents';
import useJourneyTracking from '@/composables/useJourneyTracking';

const form = useForm('patch', '/shop/basket', {
  discount: '',
}) as InertiaForm<{ discount: string }>;

const applyDiscountCode = () => {
  form.submit({
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      nextTick(() => {
        useGoogleEvents().googleEvent('event', 'checkout_progress', {
          event_category: 'applied-discount',
          event_label: `applied-discount-${form.discount}`,
        });

        useJourneyTracking().logEvent(
          'clicked',
          'Checkout/Totals/DiscountCode/ApplyDiscountCode',
          {
            code: form.discount,
          },
          true,
        );

        eventBus.$emit('refresh-payment-element');
      });
    },
  });
};

const logExpandDiscountCode = () => {
  useGoogleEvents().googleEvent('event', 'checkout_progress', {
    event_label: `opened-discount-dropdown`,
  });
};
</script>

<template>
  <Disclosure
    v-slot="{ open }"
    as="div"
    class="rounded-sm border border-primary-light/60 bg-white/60 p-3"
  >
    <DisclosureButton
      class="flex w-full items-center justify-between gap-2 text-left text-sm font-semibold text-grey-dark hover:text-primary-dark"
      :class="{ 'mb-2': open }"
      @click="logExpandDiscountCode()"
    >
      <span>Got a discount code?</span>
      <ChevronDownIcon
        class="size-5 shrink-0 transition-transform"
        :class="{ 'rotate-180': open }"
      />
    </DisclosureButton>

    <transition
      enter-active-class="overflow-hidden transition-all duration-500"
      enter-from-class="transform scale-95 opacity-0 max-h-0"
      enter-to-class="transform scale-100 opacity-100 max-h-96"
      leave-active-class="overflow-hidden transition-all duration-500"
      leave-from-class="transform scale-100 opacity-100 max-h-96"
      leave-to-class="transform scale-95 opacity-0 max-h-0"
    >
      <DisclosurePanel>
        <form
          class="flex justify-between space-x-2"
          @submit.prevent="applyDiscountCode()"
        >
          <FormInput
            v-model="form.discount"
            label=""
            name="discount-code"
            placeholder="Enter your discount code..."
            hide-label
            class="w-full"
            borders
            :error="form.errors.discount"
            hide-error-background
          />

          <CoeliacButton
            as="button"
            type="submit"
            size="sm"
            label="Apply"
            classes="h-[38px]"
          />
        </form>
      </DisclosurePanel>
    </transition>
  </Disclosure>
</template>
