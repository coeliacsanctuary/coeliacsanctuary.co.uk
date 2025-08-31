<script setup lang="ts">
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { computed, nextTick, ref, watch } from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import Loader from '@/Components/Loader.vue';
import useShopStore from '@/stores/useShopStore';
import CheckoutDiscountCode from '@/Components/PageSpecific/Shop/Checkout/CheckoutDiscountCode.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import eventBus from '@/eventBus';
import { InertiaForm } from '@/types/Core';

const props = defineProps<{
  countries: FormSelectOption[];
  selectedCountry: number;
  deliveryTimescale: string;
  subtotal: string;
  postage: string;
  discount?: string;
  total: string;
  isDigitalOnly: boolean;
}>();

const store = useShopStore();

const deliveryEstimate = computed(() => {
  const method =
    props.selectedCountry === 1
      ? 'first class post'
      : 'Royal Mail International Standard';

  return `All orders are dispatched within 1 - 2 working days by ${method}, and usually arrive within ${props.deliveryTimescale} days, but this can vary. Please see Royal Mail for more details.`;
});

const countryForm = useForm('patch', '/shop/basket', {
  postage_country_id: props.selectedCountry,
}) as InertiaForm<{ postage_country_id: number }>;

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

const removeDiscountCode = () => {
  router.delete('/shop/basket/discount', {
    preserveScroll: true,
    onFinish: () => {
      void nextTick(() => {
        eventBus.$emit('refresh-payment-element');
      });
    },
  });
};

const isLoading = ref(false);

watch(
  () => countryForm.postage_country_id,
  () => {
    isLoading.value = true;

    countryForm.submit({
      preserveScroll: true,
      onSuccess: () => {
        isLoading.value = false;
        updateStore();
      },
    });
  },
);
</script>

<template>
  <div class="w-full">
    <dl class="mt-10 space-y-3">
      <div class="flex justify-between">
        <dt class="lg:max-xl:text-lg xl:text-xl">Subtotal</dt>
        <dd
          class="text-lg font-semibold lg:max-xl:text-xl xl:text-2xl"
          v-text="subtotal"
        />
      </div>
      <template v-if="isDigitalOnly === false">
        <div class="relative flex justify-between">
          <Loader
            :display="isLoading"
            absolute
            on-top
            blur
            color="secondary"
            size="size-12"
            width="border-8"
          />

          <dt class="flex-1 pr-2 xs:pr-4">
            <div
              class="flex flex-col sm:w-full sm:flex-row sm:items-center sm:space-x-3 lg:max-xl:text-lg xl:text-xl"
            >
              <span>Postage to</span>
              <FormSelect
                v-model="countryForm.postage_country_id"
                class="sm:flex-1"
                name="country"
                :options="countries"
              />
            </div>
          </dt>
          <dd
            class="shrink-0 text-lg font-semibold lg:max-xl:text-xl xl:text-2xl"
            v-text="postage"
          />
        </div>
        <div>
          <small
            class="mt-2 block leading-tight xl:text-base"
            v-text="deliveryEstimate"
          />
          <small
            v-if="selectedCountry > 1"
            class="mt-2 block font-semibold xl:text-base"
          >
            Please note, you may be required to pay any applicable customs
            charges for any items coming from the UK.
          </small>
        </div>
      </template>

      <div
        v-if="discount"
        class="flex justify-between"
      >
        <dt
          class="flex w-full items-center justify-between lg:max-xl:text-lg xl:text-xl"
        >
          <span>Discount</span>
          <XMarkIcon
            class="mr-2 h-4 w-4 cursor-pointer"
            @click="removeDiscountCode()"
          />
        </dt>
        <dd
          class="shrink-0 text-lg font-semibold lg:max-xl:text-xl xl:text-2xl"
          v-text="`-${discount}`"
        />
      </div>
      <div v-if="!discount">
        <CheckoutDiscountCode />
      </div>
      <div
        class="flex justify-between border-t border-secondary pt-3 text-xl font-semibold lg:max-xl:text-2xl xl:text-3xl"
      >
        <dt class="">Total</dt>
        <dd
          class="font-semibold"
          v-text="total"
        />
      </div>
    </dl>
  </div>
</template>
