<script setup lang="ts">
import { ShopTravelCardProductDetail } from '@/types/Shop';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { ref } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

defineProps<{
  countries: ShopTravelCardProductDetail['countries'];
  product: ShopTravelCardProductDetail['title'];
}>();

const viewAll = ref([false, false]);
</script>

<template>
  <Card class="space-y-4">
    <SubHeading classes="text-primary-dark">
      Where can I use this travel card?
    </SubHeading>

    <p class="prose max-w-none md:prose-lg">
      Here's where my <strong v-text="product" /> card could come in handy —
      countries where the languages on it are commonly spoken or understood.
      That includes places where it's the official language, as well as
      destinations where it's widely used in restaurants and tourism. It's a
      good way to explain your dietary needs clearly while travelling, even if
      you don't speak a word of it yourself.
    </p>

    <div
      class="grid gap-4"
      :class="{ 'md:grid-cols-2': countries.length > 1 }"
    >
      <div
        v-for="(country, index) in countries"
        :key="country.language"
        class="rounded-sm border border-primary-light/60 bg-primary-lightest/40 p-4"
      >
        <template v-if="country.language">
          <h3
            class="font-coeliac text-xl font-semibold text-primary-dark"
            v-text="country.language"
          />

          <div class="my-3 h-px w-full bg-primary-light/60" />
        </template>

        <ul
          :class="{
            'grid gap-2 sm:grid-cols-2 lg:grid-cols-3': countries.length === 1,
            'flex flex-col space-y-2': countries.length > 1,
          }"
        >
          <template v-for="(usableCountry, item) in country.countries">
            <li
              v-if="item < 6 || viewAll[index]"
              :key="usableCountry.country"
              class="inline-flex items-center space-x-3"
            >
              <img
                :src="
                  usableCountry.code
                    ? `https://flagcdn.com/24x18/${usableCountry.code}.png`
                    : '/images/misc/flag-fallback.png'
                "
                :alt="`${usableCountry.country} flag`"
                width="24"
                height="18"
                loading="lazy"
                class="h-[18px] w-6 shrink-0 object-cover"
              />

              <span v-text="usableCountry.country" />
            </li>
          </template>
        </ul>

        <CoeliacButton
          v-if="country.countries.length > 6"
          as="button"
          type="button"
          theme="light"
          size="lg"
          class="mt-4"
          classes="w-full justify-center text-center"
          :label="
            viewAll[index]
              ? 'Show fewer'
              : `Show ${country.countries.length - 6} more...`
          "
          bold
          @click="viewAll[index] = !viewAll[index]"
        />
      </div>
    </div>
  </Card>
</template>
