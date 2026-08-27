<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CheckoutForm,
  CheckoutFormErrors,
  ShopBasketItem,
  ShopPopularProduct,
} from '@/types/Shop';
import Heading from '@/Components/Heading.vue';
import CheckoutTotals from '@/Components/PageSpecific/Shop/Checkout/CheckoutTotals.vue';
import CheckoutDeliveryCountry from '@/Components/PageSpecific/Shop/Checkout/CheckoutDeliveryCountry.vue';
import ShopPopularProducts from '@/Components/PageSpecific/Shop/ShopPopularProducts.vue';
import { ArrowUturnLeftIcon } from '@heroicons/vue/20/solid';
import { ShoppingBagIcon } from '@heroicons/vue/24/outline';
import { FormSelectOption } from '@/Components/Forms/Props';
import ContactDetails from '@/Components/PageSpecific/Shop/Checkout/Form/ContactDetails.vue';
import { computed, nextTick, reactive, Ref, ref, watch, onMounted } from 'vue';
import type { Component } from 'vue';
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
import useJourneyTracking from '@/composables/useJourneyTracking';
import CheckOutItemsRow from '@/Components/PageSpecific/Shop/Checkout/CheckOutItemsRow.vue';
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
  warnings: undefined;
  popularProducts: ShopPopularProduct[];
};

type BasketProps = {
  has_basket: true;
  popularProducts: undefined;
  countries: FormSelectOption[];
  basket: {
    items: ShopBasketItem[];
    selected_country: number;
    delivery_timescale: string;
    subtotal: string;
    postage: string;
    fees: { fee: string; description?: string }[];
    total_fees: string;
    discount?: string;
    total: string;
  };
  payment_intent: string;
  warnings?: string[];
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
  useJourneyTracking().logEvent('other', 'Checkout/GenericError', { message });

  store.setErrors({
    basket: message,
  });
};

const submitPendingOrder = async (payload: CheckoutForm): Promise<boolean> => {
  try {
    useGoogleEvents().googleEvent('event', 'checkout_progress', {
      event_label: `submit-pending-order`,
    });

    useJourneyTracking().logEvent(
      'other',
      'Checkout/Submit/Pending',
      payload,
      true,
    );

    await axios.post('/shop/basket', payload);

    return true;
  } catch (error: unknown) {
    if (error instanceof AxiosError) {
      const axiosError: AxiosError<{ errors: Record<string, unknown> }> =
        error as AxiosError<{ errors: Record<string, unknown> }>;

      if (axiosError.status === 422 && axiosError.response?.data.errors) {
        useJourneyTracking().logEvent(
          'other',
          'Checkout/Submit/Pending/Error',
          axiosError.response.data.errors,
        );

        store.setErrors(axiosError.response.data.errors);

        return false;
      }
    }

    createGenericError();

    return false;
  }
};

const stripePayload = (payload: CheckoutForm): ConfirmPaymentData => ({
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
  useJourneyTracking().logEvent('other', 'Checkout/Pending/Revert');

  await axios.delete('/shop/basket');
};

const prepareOrder = async () => {
  // showLoader.value = true;
  await nextTick(async () => {
    const payload = store.toForm;

    if (!(await submitPendingOrder(payload))) {
      showLoader.value = false;
      eventBus.$emit('payment-failed');

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

  void nextTick(() => {
    document
      .getElementById(`checkout-step-${next}`)
      ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
};

const toggleSection = (section: SectionKeys) => {
  sections[activeSection.value].active = false;
  sections[section].active = true;
};

watch(
  errors,
  () => {
    sections.details.error = !!errors.value?.contact;
    sections.shipping.error = !!errors.value?.shipping;

    if (sections.details.error) {
      sections.details.active = true;
      sections.payment.active = false;
      activeSection.value = 'details';

      return;
    }

    if (sections.shipping.error) {
      sections.shipping.active = true;
      sections.payment.active = false;
      activeSection.value = 'shipping';
    }
  },
  { deep: true },
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
    <Heading :border="false"> Complete your order </Heading>
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

    <div class="mt-3 grid grid-cols-1 gap-4 lg:grid-cols-5">
      <Card class="lg:col-span-3">
        <div class="flex flex-col gap-6 divide-y divide-grey-off-light">
          <div
            v-for="section in sectionComponents"
            :id="`checkout-step-${section.key}`"
            :key="section.key"
            class="scroll-mt-20 md:scroll-mt-32"
          >
            <component
              :is="section.component"
              :show="sections[section.key].active"
              :completed="sections[section.key].complete"
              :error="sections[section.key].error"
              v-bind="section.additionalProps"
              @continue="completeSection(section.key, section.next)"
              @toggle="toggleSection(section.key)"
            />
          </div>

          <p
            v-if="errors?.basket"
            ref="basketError"
            class="-mt-4 border-t-0! text-lg font-semibold text-red lg:text-xl"
            v-text="errors.basket"
          />
        </div>
      </Card>

      <div
        class="max-lg:order-first lg:sticky lg:top-4 lg:col-span-2 lg:self-start"
      >
        <Card class="border border-primary-light/60 bg-primary-lightest/50!">
          <div
            v-if="warnings?.length"
            class="mb-4 flex flex-col gap-2 rounded-sm border border-red/40 bg-red/5 p-3"
          >
            <p class="font-semibold text-red">I've had to adjust your basket</p>

            <p class="text-sm text-grey-dark">
              Stock has changed since you created your basket, so I've altered
              the quantity of some of your products.
            </p>

            <ul class="text-sm font-semibold text-primary-dark">
              <li
                v-for="(warning, index) in warnings"
                :key="index"
                v-text="warning"
              />
            </ul>
          </div>

          <CheckoutDeliveryCountry
            :countries="countries as FormSelectOption[]"
            :selected-country="basket.selected_country"
            :delivery-timescale="basket.delivery_timescale"
            :has-fees="basket.fees.length > 0"
          />

          <div class="mt-4 flow-root border-t border-primary-light/60">
            <ul class="divide-y divide-primary-light/60">
              <CheckOutItemsRow
                v-for="item in basket.items"
                :key="item.id"
                :item="item"
              />
            </ul>
          </div>

          <CheckoutTotals
            :postage="basket.postage"
            :discount="basket.discount"
            :fees="basket.fees"
            :total-fees="basket.total_fees"
            :total="basket.total"
            :subtotal="basket.subtotal"
          />

          <Link
            href="/shop"
            class="mt-4 inline-flex items-center justify-center gap-2 text-sm text-grey-dark hover:text-primary-dark"
          >
            <ArrowUturnLeftIcon class="size-4" />
            <span>Continue shopping</span>
          </Link>

          <TestModeDetails />
        </Card>
      </div>
    </div>
  </template>

  <template v-else>
    <Card
      class="mt-3 flex flex-col items-center justify-center gap-3 rounded-sm bg-primary-lightest/60 py-10 text-center"
    >
      <ShoppingBagIcon class="size-12 text-primary" />

      <p class="text-lg font-semibold">Your basket is empty</p>

      <p class="max-w-md text-sm text-grey-dark">
        Once you've added something to your basket you'll be able to check out
        here.
      </p>

      <CoeliacButton
        :as="Link"
        href="/shop"
        label="Back to shop"
        size="xxl"
        theme="secondary"
        classes="mt-2"
      />
    </Card>

    <ShopPopularProducts
      v-if="popularProducts?.length"
      class="mt-4"
      :products="popularProducts"
      tracking-label="ShopCheckoutEmptyBasket"
    />
  </template>
</template>
