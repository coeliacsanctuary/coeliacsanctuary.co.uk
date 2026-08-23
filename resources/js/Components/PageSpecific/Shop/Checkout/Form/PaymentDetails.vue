<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import CheckoutStepHeader from '@/Components/PageSpecific/Shop/Checkout/CheckoutStepHeader.vue';
import PaymentWidget from '@/Components/PageSpecific/Shop/Checkout/Form/Components/PaymentWidget.vue';
import { CheckoutBillingStep } from '@/types/Shop';
import useShopStore from '@/stores/useShopStore';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import eventBus from '@/eventBus';
import useJourneyTracking from '@/composables/useJourneyTracking';

defineProps<{
  show: boolean;
  completed: boolean;
  error: boolean;
  paymentToken: string;
}>();

const emits = defineEmits(['continue', 'toggle']);

const store = useShopStore();

const billingAddressSelect = ref<'same' | 'other'>('same');

const fields = reactive<CheckoutBillingStep>({
  name: '',
  address_1: '',
  address_2: '',
  address_3: '',
  town: '',
  county: '',
  postcode: '',
  country: '',
});

const billingDetails = (): CheckoutBillingStep => {
  if (billingAddressSelect.value === 'other') {
    return { ...fields };
  }

  return {
    name: store.customerName,
    country: store.selectedCountry,
    ...store.shippingDetails,
  };
};

const selectOptions: FormSelectOption[] = [
  { value: 'same', label: 'Same as shipping address' },
  { value: 'other', label: 'Other' },
];

const submitting = ref(false);

const paymentValid = ref(false);

const submit = () => {
  const details = billingDetails();

  submitting.value = true;
  store.setBillingDetails(details);

  useJourneyTracking().logEvent(
    'clicked',
    'Checkout/Form/PaymentDetails/Submit',
    details,
    true,
  );

  emits('continue');
};

const canSubmit = computed((): boolean => {
  if (billingAddressSelect.value === 'other') {
    if (fields.name === '') {
      return false;
    }

    if (fields.address_1 === '') {
      return false;
    }

    if (fields.town === '') {
      return false;
    }

    if (fields.postcode === '') {
      return false;
    }

    if (fields.country === '') {
      return false;
    }
  }

  if (!paymentValid.value) {
    return false;
  }

  return true;
});

watch(billingAddressSelect, () => {
  useJourneyTracking().logEvent(
    'clicked',
    'Checkout/Form/PaymentDetails/BillingAddress',
    { value: billingAddressSelect.value },
  );

  if (billingAddressSelect.value === 'same') {
    return;
  }

  Object.assign(fields, {
    name: '',
    address_1: '',
    address_2: '',
    address_3: '',
    town: '',
    county: '',
    postcode: '',
    country: '',
  });
});

eventBus.$on('payment-failed', () => {
  submitting.value = false;
});

const track = (label: string, value?: string) => {
  useJourneyTracking().logEvent(
    'typed',
    `Checkout/Form/PaymentDetails/${label}`,
    {
      value,
    },
    true,
  );
};
</script>

<template>
  <div class="flex flex-col space-y-6 pt-4">
    <CheckoutStepHeader
      :step="3"
      title="Payment"
      :show="show"
      :completed="completed"
      :error="error"
      @toggle="$emit('toggle')"
    />

    <template v-if="show">
      <p class="prose mt-2! max-w-none xl:prose-lg">
        Thanks for letting me know where you want your order shipped, finally I
        need to know how you'd like to pay.
      </p>

      <FormSelect
        v-model="billingAddressSelect"
        label="Billing Address"
        name="billing_address"
        :options="selectOptions"
        :placeholder="undefined"
      />

      <template v-if="billingAddressSelect === 'other'">
        <FormInput
          v-model="fields.name"
          label="Card Holders Name"
          name="address_1"
          autocomplete="name"
          required
          borders
          @blur-sm="() => track('Name', fields.name)"
        />

        <FormInput
          v-model="fields.address_1"
          label="Address (Line 1)"
          name="address_1"
          autocomplete="address_1"
          required
          borders
          @blur-sm="() => track('Address1', fields.address_1)"
        />

        <FormInput
          v-model="fields.address_2"
          label="Address (Line 2)"
          name="address_2"
          autocomplete="address_2"
          borders
          @blur-sm="() => track('Address2', fields.address_2)"
        />

        <FormInput
          v-model="fields.address_3"
          label="Address (Line 3)"
          name="address_3"
          autocomplete="address_3"
          borders
          @blur-sm="() => track('Address3', fields.address_3)"
        />

        <FormInput
          v-model="fields.town"
          label="Town / City"
          name="town"
          autocomplete="town"
          required
          borders
          @blur-sm="() => track('Town', fields.town)"
        />

        <FormInput
          v-model="fields.county"
          label="County / State / Province"
          name="county"
          autocomplete="county"
          borders
          @blur-sm="() => track('County', fields.county)"
        />

        <FormInput
          v-model="fields.postcode"
          label="Postcode / Zipcode"
          name="postcode"
          autocomplete="postcode"
          required
          borders
          @blur-sm="() => track('Postcode', fields.postcode)"
        />

        <FormInput
          v-model="fields.country"
          label="Country"
          name="country"
          autocomplete="country"
          required
          borders
          @blur-sm="() => track('Country', fields.country)"
        />
      </template>

      <Suspense>
        <PaymentWidget
          :payment-token="paymentToken"
          @payment-ready="paymentValid = true"
          @payment-not-valid="paymentValid = false"
        />
      </Suspense>

      <CoeliacButton
        as="button"
        type="button"
        label="Pay Now!"
        size="xxl"
        classes="px-6! text-xl justify-between"
        theme="secondary"
        :icon="ArrowRightIcon"
        icon-position="right"
        :disabled="!canSubmit"
        :loading="submitting"
        @click="submit()"
      />

      <p class="flex items-center justify-center gap-2 text-sm text-grey-dark">
        <LockClosedIcon class="size-4 shrink-0" />
        <span
          >Payments are handled by Stripe - I never see your card details.</span
        >
      </p>
    </template>
  </div>
</template>
