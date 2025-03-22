<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { CheckoutForm, CheckoutFormErrors, ShopBasketItem } from '@/types/Shop';
import Heading from '@/Components/Heading.vue';
import CheckoutItems from '@/Components/PageSpecific/Shop/Checkout/CheckoutItems.vue';
import CheckoutTotals from '@/Components/PageSpecific/Shop/Checkout/CheckoutTotals.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import ContactDetails from '@/Components/PageSpecific/Shop/Checkout/Form/ContactDetails.vue';
import {
  computed,
  nextTick,
  reactive,
  Ref,
  ref,
  watch,
  onMounted,
  Component,
} from 'vue';
import ShippingDetails from '@/Components/PageSpecific/Shop/Checkout/Form/ShippingDetails.vue';
import useShopStore from '@/stores/useShopStore';
import useLocalStorage from '@/composables/useLocalStorage';
import PaymentDetails from '@/Components/PageSpecific/Shop/Checkout/Form/PaymentDetails.vue';
import Loader from '@/Components/Loader.vue';
import useUrl from '@/composables/useUrl';
import axios, { AxiosError } from 'axios';
import useStripeStore from '@/stores/useStripeStore';
import en from 'i18n-iso-countries/langs/en.json';
import { Link, usePage } from '@inertiajs/vue3';
import eventBus from '@/eventBus';
import { ConfirmPaymentData } from '@stripe/stripe-js';
import useGoogleEvents from '@/composables/useGoogleEvents';
import pkg from 'i18n-iso-countries';
import TestModeDetails from '@/Components/PageSpecific/Shop/Checkout/TestModeDetails.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
const { registerLocale, getAlpha2Code } = pkg;

type SectionKeys = 'details' | 'shipping' | 'payment' | '_complete';
type FormSection = {
  [T in SectionKeys]: {
    active: boolean;
    complete: boolean;
    error: boolean;
  };
};

type SectionComponent = {
  component: Component;
  key: SectionKeys;
  next: SectionKeys;
  additionalProps: Record<string, unknown>;
};

type NoBasketProps = {
  has_basket: false;
  countries: undefined;
  basket: undefined;
  payment_intent: undefined;
};

type BasketProps = {
  has_basket: true;
  countries: FormSelectOption[];
  basket: {
    items: ShopBasketItem[];
    selected_country: number;
    delivery_timescale: string;
    subtotal: string;
    postage: string;
    discount?: string;
    total: string;
  };
  payment_intent: string;
};

registerLocale(en);

const showLoader = ref(false);

const props = defineProps<NoBasketProps | BasketProps>();
const store = useShopStore();
const errors = computed<Partial<CheckoutFormErrors>>(() => store.getErrors);

const basketError = ref<HTMLParagraphElement | null>(null);

if (usePage().props.errors) {
  store.setErrors(usePage().props.errors);

  nextTick(() => {
    basketError.value?.scrollIntoView({ behavior: 'smooth' });
  });

  eventBus.$on('payment-widget-loaded', () => {
    basketError.value?.scrollIntoView({ behavior: 'smooth' });
  });
}

const { getFromLocalStorage, putInLocalStorage } = useLocalStorage();

const createGenericError = (
  message: string = 'An unknown error has occurred, you have not been charged.',
): void => {
  store.setErrors({
    basket: message,
  });
};

const submitPendingOrder = async (payload: CheckoutForm): Promise<boolean> => {
  try {
    useGoogleEvents().googleEvent('event', 'checkout_progress', {
      event_label: `submit-pending-order`,
    });

    await axios.post('/shop/basket', payload);

    return true;
  } catch (error: unknown) {
    if (error instanceof AxiosError) {
      const axiosError: AxiosError<{ errors: Record<string, unknown> }> =
        error as AxiosError<{ errors: Record<string, unknown> }>;

      if (axiosError.status === 422 && axiosError.response?.data.errors) {
        store.setErrors(axiosError.response.data.errors);

        return false;
      }
    }

    createGenericError();

    return false;
  }
};

const stripePayload = (payload: CheckoutForm): Partial<ConfirmPaymentData> => ({
  return_url: useUrl().generateUrl('done'),
  payment_method_data: {
    billing_details: {
      name: payload.billing.name,
      email: payload.contact.email,
      phone: payload.contact.phone,
      address: {
        line1: payload.billing.address_1,
        line2:
          payload.billing.address_2 +
          (payload.billing.address_3 ? `, ${payload.billing.address_3}` : ''),
        city: payload.billing.town,
        state: payload.billing.county,
        postal_code: payload.billing.postcode,
        country:
          getAlpha2Code(payload.billing.country, 'en') ||
          payload.billing.country,
      },
    },
  },
});

const revertPendingOrder = async (): Promise<void> => {
  await axios.delete('/shop/basket');
};

const prepareOrder = async () => {
  // showLoader.value = true;
  await nextTick(async () => {
    const payload = store.toForm;

    if (!(await submitPendingOrder(payload))) {
      return;
    }

    const stripeStore = useStripeStore();

    await stripeStore.instantiate(props.payment_intent as string);

    const { error } = await stripeStore.stripe.confirmPayment({
      elements: stripeStore.elements,
      confirmParams: stripePayload(payload),
      redirect: 'always',
    });

    if (error?.type === 'card_error' || error?.type === 'validation_error') {
      createGenericError(error.message);
    } else {
      createGenericError();
    }

    await revertPendingOrder();

    showLoader.value = false;
    eventBus.$emit('payment-failed');
  });
};

let existingForm = getFromLocalStorage<Partial<CheckoutForm>>('checkout-form');

if (existingForm) {
  store.setForm(existingForm);
}

const sections: FormSection = reactive(
  getFromLocalStorage<FormSection>('checkout-steps', <FormSection>{
    details: {
      active: true,
      complete: false,
      error: false,
    },
    shipping: {
      active: false,
      complete: false,
      error: false,
    },
    payment: {
      active: false,
      complete: false,
      error: false,
    },
  }) as FormSection,
);

const activeSection: Ref<SectionKeys> = ref(
  getFromLocalStorage<SectionKeys>(
    'checkout-active-section',
    'details',
  ) as SectionKeys,
);

const completeSection = async (section: SectionKeys, next: SectionKeys) => {
  putInLocalStorage('checkout-form', store.toForm);

  useGoogleEvents().googleEvent('event', 'complete-checkout-section', {
    checkout_step: section,
    next_step: next,
  });

  if (next === '_complete') {
    sections.payment = {
      active: true,
      complete: true,
      error: false,
    };

    await prepareOrder();

    return;
  }

  sections[section] = {
    active: false,
    complete: true,
    error: false,
  };

  sections[next] = {
    active: true,
    complete: false,
    error: false,
  };

  activeSection.value = next;

  putInLocalStorage('checkout-steps', sections);
  putInLocalStorage('checkout-active-section', next);
};

const toggleSection = (section: SectionKeys) => {
  sections[activeSection.value].active = false;
  sections[section].active = true;
};

watch(
  () => errors,
  () => {
    store.setErrors(errors.value);

    sections.details.error = !!errors.value?.contact;
    sections.shipping.error = !!errors.value?.shipping;

    if (sections.details.error) {
      sections.details.active = true;
      sections.payment.active = false;

      return;
    }

    if (sections.shipping.error) {
      sections.shipping.active = true;
      sections.payment.active = false;
    }
  },
);

const sectionComponents: SectionComponent[] = [
  {
    component: ContactDetails as Component,
    key: 'details',
    next: 'shipping',
    additionalProps: {},
  },
  {
    component: ShippingDetails as Component,
    key: 'shipping',
    next: 'payment',
    additionalProps: {
      canLookupPostcode: props.basket?.selected_country === 1,
    },
  },
  {
    component: PaymentDetails as Component,
    key: 'payment',
    next: '_complete',
    additionalProps: {
      paymentToken: props.payment_intent,
    },
  },
];

onMounted(() => {
  useGoogleEvents().googleEvent('event', 'begin_checkout', {
    items: props.basket?.items.map((item: ShopBasketItem) => ({
      id: item.id,
      name: item.title,
      variant: item.variant ?? '',
      quantity: item.quantity,
      price: item.line_price,
    })),
  });
});
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{ href: '/shop', label: 'Continue shopping.' }"
      :border="false"
    >
      Complete your order
    </Heading>
  </Card>

  <template v-if="has_basket && basket">
    <Loader
      :display="showLoader"
      class="fixed h-screen w-screen"
      blur
      size="size-24"
      on-top
      color="secondary"
      width="border-[12px]"
      :absolute="false"
    />

    <div
      class="grid grid-cols-1 gap-x-4 gap-y-4 lg:max-xl:grid-cols-2 xl:grid-cols-3"
    >
      <Card
        theme="primary-light"
        class="bg-primary-light/20!"
      >
        <CheckoutItems :items="basket.items" />
        <CheckoutTotals
          :countries="countries as FormSelectOption[]"
          :selected-country="basket.selected_country"
          :delivery-timescale="basket.delivery_timescale"
          :postage="basket.postage"
          :discount="basket.discount"
          :total="basket.total"
          :subtotal="basket.subtotal"
        />

        <TestModeDetails />
      </Card>

      <Card class="xl:col-span-2">
        <div class="flex flex-col gap-5 divide-y divide-grey-off-light">
          <component
            :is="section.component"
            v-for="section in sectionComponents"
            :key="section.key"
            :show="sections[section.key].active"
            :completed="sections[section.key].complete"
            :error="sections[section.key].error"
            v-bind="section.additionalProps"
            @continue="completeSection(section.key, section.next)"
            @toggle="toggleSection(section.key)"
          />

          <p
            v-if="errors?.basket"
            ref="basketError"
            class="-mt-4 border-t-0! text-lg font-semibold text-red lg:text-xl"
            v-text="errors.basket"
          />
        </div>
      </Card>
    </div>
  </template>

  <template v-else>
    <Card class="flex flex-col items-center justify-center space-y-4 py-8">
      <p class="prose prose-xl text-center font-semibold">
        You haven't got any items in your basket!
      </p>

      <CoeliacButton
        :as="Link"
        href="/shop"
        label="Back to shop"
        size="xxl"
        theme="secondary"
      />
    </Card>
  </template>
</template>
