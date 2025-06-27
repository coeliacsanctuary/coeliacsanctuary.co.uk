<script setup lang="ts">
import FormInput from '@/Components/Forms/FormInput.vue';
import { computed, ComputedRef, reactive, ref, watch } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon, CheckIcon } from '@heroicons/vue/24/outline';
import useShopStore from '@/stores/useShopStore';
import AddressLookup from '@/Components/PageSpecific/Shop/Checkout/Form/Components/AddressLookup.vue';
import { CheckoutShippingStep } from '@/types/Shop';
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid';
import { storeToRefs } from 'pinia';

defineProps<{ show: boolean; completed: boolean; error: boolean }>();

const emits = defineEmits(['continue', 'toggle']);

const store = useShopStore();

const { selectedCountry: country } = storeToRefs(store);

const data = reactive({ ...store.shippingDetails });

const errors: ComputedRef<Partial<CheckoutShippingStep>> = computed(
  () => store.getErrors.shipping || {},
);

const disableButton = computed((): boolean => {
  if (data.address_1 === '') {
    return true;
  }

  if (data.town === '') {
    return true;
  }

  if (data.postcode === '') {
    return true;
  }

  return false;
});

const handleAddressLookup = (address: CheckoutShippingStep) => {
  data.address_1 = address.address_1;
  data.address_2 = address.address_2;
  data.address_3 = address.address_3;
  data.town = address.town;
  data.county = address.county;
  data.postcode = address.postcode;
};

const addressActive = ref(false);

const postcodeLabel = computed(() => {
  switch (country.value) {
    case 'United Kingdom':
    case 'Republic of Ireland':
      return 'Postcode';
    case 'Canada':
      return 'Postal Code';
    default:
      return 'Zipcode';
  }
});

const countyLabel = computed(() => {
  switch (country.value) {
    case 'United States':
    case 'Australia':
      return 'State';
    case 'United Kingdom':
    case 'Republic of Ireland':
      return 'County';
    case 'New Zealand':
      return 'Region';
    default:
      return 'Province';
  }
});

watch(data, () => {
  store.setShippingDetails(data);
});

const submitForm = () => {
  if (disableButton.value) {
    return;
  }

  emits('continue');
};
</script>

<template>
  <div class="flex flex-col space-y-6 pt-4">
    <h2
      class="flex justify-between text-3xl font-semibold"
      :class="{
        'cursor-pointer text-primary-dark': !error && (show || completed),
        'text-grey-off': !error && !show,
        'text-red': error,
      }"
      @click="completed ? $emit('toggle') : undefined"
    >
      <span>Shipping Details</span>
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
      class="flex flex-col space-y-6 pt-4"
      @keyup.enter="submitForm()"
    >
      <p class="prose mt-2! max-w-none xl:prose-lg">
        Thanks {{ store.customerName }}, next we need to know where to send your
        order.
      </p>

      <AddressLookup
        :address="data.address_1"
        :active="addressActive"
        @set-address="handleAddressLookup"
      >
        <FormInput
          v-model="data.address_1"
          :error="errors?.address_1"
          label="Address (Line 1)"
          name="address_1"
          autocomplete="off"
          required
          borders
          @focus="addressActive = true"
          @blur="addressActive = false"
        />
      </AddressLookup>

      <FormInput
        v-model="data.address_2"
        :error="errors?.address_2"
        label="Address (Line 2)"
        name="address_2"
        autocomplete="address_2"
        borders
      />

      <FormInput
        v-model="data.address_3"
        :error="errors?.address_3"
        label="Address (Line 3)"
        name="address_3"
        autocomplete="address_3"
        borders
      />

      <FormInput
        v-model="data.town"
        :error="errors?.town"
        label="Town / City"
        name="town"
        autocomplete="town"
        required
        borders
      />

      <FormInput
        v-model="data.county"
        :error="errors?.county"
        :label="countyLabel"
        name="county"
        autocomplete="county"
        borders
      />

      <FormInput
        v-model="data.postcode"
        :error="errors?.postcode"
        :label="postcodeLabel"
        name="postcode"
        autocomplete="postcode"
        class="flex-1"
        required
        borders
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
