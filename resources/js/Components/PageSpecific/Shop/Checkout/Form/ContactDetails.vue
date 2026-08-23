<script setup lang="ts">
import FormInput from '@/Components/Forms/FormInput.vue';
import { computed, ComputedRef, reactive, watch } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import CheckoutStepHeader from '@/Components/PageSpecific/Shop/Checkout/CheckoutStepHeader.vue';
import useShopStore from '@/stores/useShopStore';
import { CheckoutContactStep } from '@/types/Shop';
import axios, { AxiosError } from 'axios';
import useJourneyTracking from '@/composables/useJourneyTracking';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';

defineProps<{ show: boolean; completed: boolean; error: boolean }>();
const emits = defineEmits(['continue', 'toggle']);

const store = useShopStore();

const data = reactive({ ...store.userDetails });
const errors: ComputedRef<Partial<CheckoutContactStep>> = computed(
  () => store.getErrors.contact || {},
);

const disableButton = computed((): boolean => {
  if (!data) {
    return true;
  }

  if (data.name === '') {
    return true;
  }

  if (data.email === '') {
    return true;
  }

  return false;
});

const summary = computed((): string[] =>
  [data.name, data.email].filter((line) => line !== ''),
);

const storeCustomerDetails = async (): Promise<void> => {
  try {
    await axios.patch('/shop/basket', { contact: store.userDetails });
  } catch (error: unknown) {
    if (error instanceof AxiosError) {
      const axiosError: AxiosError<{ errors: Record<string, unknown> }> =
        error as AxiosError<{ errors: Record<string, unknown> }>;

      if (axiosError.status === 422 && axiosError.response?.data.errors) {
        store.setErrors(axiosError.response.data.errors);
      }
    }
  }
};

const submitForm = () => {
  if (disableButton.value) {
    return;
  }

  useJourneyTracking().logEvent(
    'clicked',
    'Checkout/Form/ContactDetails/Submit',
    data,
    true,
  );

  storeCustomerDetails();

  emits('continue');
};

watch(data, () => store.setUserDetails(data));

const track = (label: string, value?: string) => {
  useJourneyTracking().logEvent(
    'typed',
    `Checkout/Form/ContactDetails/${label}`,
    {
      value,
    },
    true,
  );
};
</script>

<template>
  <div class="flex flex-col space-y-6">
    <CheckoutStepHeader
      :step="1"
      title="Your details"
      :show="show"
      :completed="completed"
      :error="error"
      :summary="summary"
      @toggle="$emit('toggle')"
    />

    <form
      v-if="show"
      class="flex flex-col space-y-6"
      @keyup.enter="submitForm()"
    >
      <p class="prose mt-2! max-w-none xl:prose-lg">
        To get started I just need a few basic details - your name, the email
        address I'll send your order confirmation to, and optionally, a
        telephone number.
      </p>

      <FormInput
        v-model="data.name"
        :error="errors.name"
        label="Your name"
        name="name"
        autocomplete="name"
        required
        borders
        @blur-sm="() => track('Name', data.name)"
      />

      <FormInput
        v-model="data.email"
        :error="errors.email"
        type="email"
        label="Your Email"
        name="email"
        autocomplete="email"
        required
        borders
        @blur-sm="() => track('Email', data.email)"
      />

      <FormInput
        v-model="data.phone"
        :error="errors.phone"
        label="Your Phone Number"
        name="phone"
        autocomplete="phone"
        type="phone"
        borders
        @blur-sm="() => track('Phone', data.phone)"
      />

      <FormCheckbox
        v-model="data.subscribeToNewsletter"
        :error="errors.subscribeToNewsletter"
        label="Would you like to subscribe to my newsletter?"
        name="subscribeToNewsletter"
        layout="left"
        xl
        highlight
      />

      <CoeliacButton
        as="button"
        type="button"
        label="Continue..."
        size="xxl"
        classes="px-6! text-xl justify-between"
        theme="secondary"
        :icon="ArrowRightIcon"
        icon-position="right"
        :disabled="disableButton"
        @click="submitForm()"
      />
    </form>
  </div>
</template>
