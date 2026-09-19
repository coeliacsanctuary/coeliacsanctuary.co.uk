<script setup lang="ts">
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import { ShopDigitalCard } from '@/types/Shop';
import { computed, ref, useTemplateRef } from 'vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import CountryFlag from '@/Components/CountryFlag.vue';
import DigitalCardCountryList from '@/Components/PageSpecific/Shop/DigitalCards/DigitalCardCountryList.vue';
import AddDigitalCardToBasket from '@/Components/PageSpecific/Shop/DigitalCards/AddDigitalCardToBasket.vue';

const props = withDefaults(
  defineProps<{ product: ShopDigitalCard; eager?: boolean }>(),
  { eager: false },
);

const countries = computed((): string => {
  if (!props.product.countries) {
    return '';
  }

  return props.product.countries
    .map((country) => country.country)
    .splice(0, 5)
    .join(', ');
});

const hasMoreCountries = computed((): false | number => {
  if (!props.product.countries) {
    return false;
  }

  if (props.product.countries.length <= 5) {
    return false;
  }

  return props.product.countries.length - 5;
});

const showCountryListSidebar = ref(false);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'DigitalProductCard',
  {
    title: props.product.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="w-full overflow-hidden"
  >
    <div class="group flex flex-1 flex-col space-y-3">
      <div class="flex items-center">
        <CountryFlag
          :code="product.flag"
          :country="product.language"
        />

        <h2
          class="line-clamp-2 text-xl font-semibold text-primary-dark transition md:text-2xl"
          v-text="product.title"
        />
      </div>

      <p
        v-if="product.countries?.length"
        class="mt-auto font-semibold"
      >
        Can be used in {{ countries }}
        <a
          v-if="hasMoreCountries"
          class="cursor-pointer text-primary-dark transition hover:text-black hover:underline"
          @click.prevent="showCountryListSidebar = true"
        >
          and {{ hasMoreCountries }} more
          {{ pluralise('country', hasMoreCountries) }}
        </a>
      </p>

      <p class="text-sm text-grey-dark">
        A {{ product.language }} digital travel and translation card for eating
        out with Coeliac disease, that can be accessed anywhere on your mobile
        device.
      </p>

      <p
        v-if="product.product"
        class="text-sm text-grey-darkest"
      >
        Want the physical card instead? Check out my
        <a
          class="font-semibold text-primary-dark transition hover:text-black"
          :href="product.product.link"
          v-text="product.product.title"
        />.
      </p>
    </div>

    <div
      class="-mx-4 mt-4 -mb-4 flex items-center justify-between bg-primary-lightest/60 p-4"
    >
      <div class="flex flex-col gap-3">
        <div class="flex items-center space-x-4">
          <p
            v-if="product.product && product.product.isInBasket"
            class="font-semibold text-red-dark line-through md:max-xl:text-lg"
            v-text="product.price.default"
          />

          <p
            class="text-2xl font-semibold md:max-xl:text-3xl xl:text-4xl"
            v-text="
              product.product?.isInBasket
                ? product.price.extra
                : product.price.default
            "
          />
        </div>

        <p
          v-if="product.product && product.product.isInBasket"
          class="mr-4 text-xs text-grey-darker"
        >
          You've already got my
          <span
            class="font-semibold"
            v-text="product.product.title"
          />
          in your basket, save
          <span
            class="font-semibold"
            v-text="product.price.saving"
          />
          on getting a digital bundle!
        </p>
      </div>

      <div class="shrink-0">
        <AddDigitalCardToBasket :product="product" />
      </div>
    </div>
  </Card>

  <DigitalCardCountryList
    v-if="product.countries?.length"
    :title="product.title"
    :countries="product.countries"
    :show="showCountryListSidebar"
    @close="showCountryListSidebar = false"
  />
</template>
