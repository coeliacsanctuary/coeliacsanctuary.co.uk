<script setup lang="ts">
import FormInput from '@/Components/Forms/FormInput.vue';
import { computed, ComputedRef, reactive, watch } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid';
import useShopStore from '@/stores/useShopStore';
import { CheckoutContactStep } from '@/types/Shop';
import axios, { AxiosError } from 'axios';
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

  if (data.email !== data.email_confirmation) {
    return true;
  }

  return false;
});

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

  storeCustomerDetails();

  emits('continue');
};

watch(data, () => store.setUserDetails(data));
</script>

<template>
  <div class="flex flex-col space-y-6">
    <h2
      class="flex items-center justify-between text-3xl font-semibold"
      :class="{
        'cursor-pointer text-primary-dark': !error && (show || completed),
        'text-grey-off': !error && !show,
        'text-red': error,
      }"
      @click="completed ? $emit('toggle') : undefined"
    >
      <span>Your Details</span>
      <CheckIcon
        v-if="completed && !error"
        class="h-8 w-8 text-green"
      />
      <ExclamationCircleIcon
        v-if="error"
        class="h-8 w-8 text-red"
      />
    </h2>

    <form
      v-if="show"
      class="flex flex-col space-y-6"
      @keyup.enter="submitForm()"
    >
      <p class="prose mt-2! max-w-none xl:prose-lg">
        To start the checkout process we just need some basic details from you
        including your name, the email address for your order confirmation, and
        optionally, a telephone number.
      </p>

      <FormInput
        v-model="data.name"
        :error="errors.name"
        label="Your name"
        name="name"
        autocomplete="name"
        required
        borders
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
      />

      <FormInput
        v-model="data.email_confirmation"
        :error="errors.email_confirmation"
        type="email"
        label="Confirm Email"
        name="email_confirmation"
        autocomplete="email"
        required
        borders
      />

      <FormInput
        v-model="data.phone"
        :error="errors.phone"
        label="Your Phone Number"
        name="phone"
        autocomplete="phone"
        type="phone"
        borders
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
