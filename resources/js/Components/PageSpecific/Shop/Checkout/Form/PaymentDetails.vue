<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon, CheckIcon } from '@heroicons/vue/24/outline';
import PaymentWidget from '@/Components/PageSpecific/Shop/Checkout/Form/Components/PaymentWidget.vue';
import { CheckoutBillingStep } from '@/types/Shop';
import useShopStore from '@/stores/useShopStore';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import eventBus from '@/eventBus';

defineProps<{ show: boolean; completed: boolean; paymentToken: string }>();

const emits = defineEmits(['continue', 'toggle']);

const store = useShopStore();

const shippingDetails = computed(() => store.shippingDetails);

const fields = reactive<CheckoutBillingStep>({
  name: store.customerName,
  country: store.selectedCountry,
  ...shippingDetails.value,
});

const billingAddressSelect = ref<'same' | 'other'>('same');

const selectOptions: FormSelectOption[] = [
  { value: 'same', label: 'Same as shipping address' },
  { value: 'other', label: 'Other' },
];

const submitting = ref(false);

const paymentValid = ref(false);

const submit = () => {
  submitting.value = true;
  store.setBillingDetails(fields);

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
  if (billingAddressSelect.value === 'same') {
    fields = reactive({
      name: store.customerName,
      country: store.selectedCountry,
      ...store.shippingDetails,
    });

    return;
  }

  fields.name = '';
  fields.address_1 = '';
  fields.address_2 = '';
  fields.address_3 = '';
  fields.town = '';
  fields.county = '';
  fields.postcode = '';
  fields.country = '';
});

eventBus.$on('payment-failed', () => {
  submitting.value = false;
});
</script>

<template>
  <div class="flex flex-col space-y-6 pt-4">
    <h2
      class="flex justify-between text-3xl font-semibold"
      :class="{
        'text-primary-dark': show || completed,
        'text-grey-off': !show,
      }"
      @click="completed ? $emit('toggle') : undefined"
    >
      <span>Payment Details</span>
      <CheckIcon
        v-if="completed"
        class="h-8 w-8 text-green"
      />
    </h2>

    <template v-if="show">
      <p class="prose mt-2! max-w-none xl:prose-lg">
        Thanks for letting us know where you went your order shipped, finally we
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
        />

        <FormInput
          v-model="fields.address_1"
          label="Address (Line 1)"
          name="address_1"
          autocomplete="address_1"
          required
          borders
        />

        <FormInput
          v-model="fields.address_2"
          label="Address (Line 2)"
          name="address_2"
          autocomplete="address_2"
          borders
        />

        <FormInput
          v-model="fields.address_3"
          label="Address (Line 3)"
          name="address_3"
          autocomplete="address_3"
          borders
        />

        <FormInput
          v-model="fields.town"
          label="Town / City"
          name="town"
          autocomplete="town"
          required
          borders
        />

        <FormInput
          v-model="fields.county"
          label="County / State / Province"
          name="county"
          autocomplete="county"
          borders
        />

        <FormInput
          v-model="fields.postcode"
          label="Postcode / Zipcode"
          name="postcode"
          autocomplete="postcode"
          required
          borders
        />

        <FormInput
          v-model="fields.country"
          label="Country"
          name="country"
          autocomplete="country"
          required
          borders
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
    </template>
  </div>
</template>
