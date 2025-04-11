<script setup lang="ts">
import eventBus from '@/eventBus';
import useStripeStore from '@/stores/useStripeStore';
import { StripePaymentElementChangeEvent } from '@stripe/stripe-js';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps<{ paymentToken: string }>();

const emits = defineEmits(['payment-ready', 'payment-not-valid']);

const stripeStore = useStripeStore();

const init = () => {
  stripeStore.instantiate(props.paymentToken).then(() => {
    const paymentElement = stripeStore.elements.create('payment', {
      layout: {
        type: 'accordion',
        defaultCollapsed: false,
        radios: false,
        spacedAccordionItems: true,
      },
      fields: {
        billingDetails: 'never',
      },
    });

    paymentElement.mount('#stripe');

    eventBus.$on('refresh-payment-element', () => {
      stripeStore.elements.fetchUpdates();
    });

    paymentElement.on('change', (event: StripePaymentElementChangeEvent) => {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      if (event.complete) {
        emits('payment-ready');
        return;
      }

      emits('payment-not-valid');
    });

    paymentElement.on('ready', () => {
      eventBus.$emit('payment-widget-loaded');
    });
  });
};

onMounted(() => {
  init();
});

onUnmounted(() => {
  stripeStore.reset();
});
</script>

<template>
  <div>
    <div id="stripe" />
  </div>
</template>
