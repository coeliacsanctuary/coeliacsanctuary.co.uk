<script setup lang="ts">
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import Loader from '@/Components/Loader.vue';
import useShopStore from '@/stores/useShopStore';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useForm } from 'laravel-precognition-vue-inertia';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  countries: FormSelectOption[];
  selectedCountry: number;
  deliveryTimescale: string;
  hasFees: boolean;
}>();

const store = useShopStore();

const countryForm = useForm<{ postage_country_id: number }>(
  'patch',
  '/shop/basket',
  {
    postage_country_id: props.selectedCountry,
  },
);

const updateStore = () => {
  const selectedOption: FormSelectOption | undefined = props.countries.find(
    (country) => country.value === countryForm.postage_country_id,
  );

  if (!selectedOption) {
    return;
  }

  store.setCountry(<string>selectedOption.label);
};

updateStore();

const isLoading = ref(false);

const deliveryEstimate = computed(() => {
  const method =
    props.selectedCountry === 1
      ? 'first class post'
      : 'Royal Mail International Standard';

  return `Dispatched within 1 - 2 working days by ${method}, usually arriving within ${props.deliveryTimescale} days.`;
});

watch(
  () => countryForm.postage_country_id,
  () => {
    isLoading.value = true;

    countryForm.submit({
      preserveScroll: true,
      onSuccess: () => {
        isLoading.value = false;
        updateStore();

        useJourneyTracking().logEvent(
          'clicked',
          'Checkout/Totals/ChangeCountry',
          {
            country: countryForm.postage_country_id,
          },
        );
      },
    });
  },
);
</script>

<template>
  <div class="relative flex flex-col gap-2">
    <Loader
      :display="isLoading"
      absolute
      on-top
      blur
      color="secondary"
      size="size-10"
      width="border-4"
    />

    <label
      class="text-sm font-semibold text-grey-dark"
      for="country"
    >
      Delivering to
    </label>

    <FormSelect
      v-model="countryForm.postage_country_id"
      name="country"
      :options="countries"
    />

    <p
      class="text-sm text-grey-dark"
      v-text="deliveryEstimate"
    />

    <p
      v-if="selectedCountry > 1"
      class="text-sm font-semibold text-grey-dark"
    >
      <template v-if="!hasFees">
        Please note, you may be required to pay any applicable customs charges
        for items coming from the UK.
      </template>
      <template v-else>
        Any required fees have been applied, but you may also need to pay
        additional customs charges for items coming from the UK.
      </template>
    </p>
  </div>
</template>
