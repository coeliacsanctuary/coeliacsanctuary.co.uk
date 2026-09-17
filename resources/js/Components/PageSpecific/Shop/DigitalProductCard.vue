<script setup lang="ts">
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import { ShopDigitalCard } from '@/types/Shop';
import { computed, ref, useTemplateRef } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useAddToBasket from '@/composables/useAddToBasket';
import useJourneyTracking from '@/composables/useJourneyTracking';
import Sidebar from '@/Components/Overlays/Sidebar.vue';

const props = withDefaults(
  defineProps<{ product: ShopDigitalCard; eager?: boolean }>(),
  { eager: false },
);

// const { addBasketForm, prepareAddBasketForm, submitAddBasketForm } =
//   useAddToBasket();
//
// prepareAddBasketForm(props.product.id, props.product.primary_variant);
//
// const addToBasket = () => {
//   useJourneyTracking().logEvent('clicked', 'ShopProductCard/AddToBasket', {
//     title: props.product.title,
//     id: props.product.id,
//   });
//
//   submitAddBasketForm({
//     only: ['basket'],
//   });
// };

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
        <img
          :src="
            product.flag
              ? `https://flagcdn.com/24x18/${product.flag}.png`
              : '/images/misc/flag-fallback.png'
          "
          :alt="`${product.language} flag`"
          width="24"
          height="18"
          loading="lazy"
          class="mr-4 h-[18px] w-6 shrink-0 object-cover"
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
      <div class="flex items-center justify-between gap-3">
        <p
          class="text-2xl font-semibold md:max-xl:text-3xl xl:text-4xl"
          v-text="product.price"
        />
      </div>

      <CoeliacButton
        as="button"
        type="button"
        label="Add to Basket"
        classes="justify-center text-center"
        theme="secondary"
        size="lg"
        :icon="ShoppingBagIcon"
        icon-position="right"
        bold
      />
    </div>
  </Card>

  <Sidebar
    v-if="product.countries?.length"
    side="right"
    :open="showCountryListSidebar"
    @close="showCountryListSidebar = false"
  >
    <Card class="flex min-h-screen flex-col space-y-3">
      <h2 class="text-lg font-semibold">
        Here's some countries where my {{ product.title }} can be used:
      </h2>

      <ul class="flex flex-col space-y-2">
        <li
          v-for="country in product.countries"
          :key="country.country"
          class="flex items-center"
        >
          <img
            :src="
              country.code
                ? `https://flagcdn.com/24x18/${country.code}.png`
                : '/images/misc/flag-fallback.png'
            "
            :alt="`${country.country} flag`"
            width="24"
            height="18"
            loading="lazy"
            class="mr-4 h-[18px] w-6 shrink-0 object-cover"
          />

          <span
            class="font-semibold"
            v-text="country.country"
          />
        </li>
      </ul>
    </Card>
  </Sidebar>
</template>
