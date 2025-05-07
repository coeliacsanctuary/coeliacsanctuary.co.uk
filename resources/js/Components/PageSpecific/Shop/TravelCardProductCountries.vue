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
  <div class="relative -mt-1 flex flex-col space-y-3 px-3">
    <Card>
      <p class="prose prose-lg max-w-none">
        Wondering where our <strong v-text="product" /> card could be useful?
        We've pulled together a list of countries where the languages on this
        card are commonly spoken or understood. That includes places where it’s
        the official language, as well as destinations where it’s widely used in
        restaurants, tourism, or everyday life. It's a great way to help explain
        your dietary needs clearly while travelling, even if you're not fluent
        yourself.
      </p>
    </Card>

    <div
      class="grid gap-3 md:gap-5 xl:gap-10"
      :class="{ 'md:grid-cols-2': countries.length > 1 }"
    >
      <Card
        v-for="(country, index) in countries"
        :key="country.language"
      >
        <template v-if="country.language">
          <SubHeading>
            {{ country.language }}
          </SubHeading>

          <div class="my-4 h-[2px] w-full bg-primary-light/50" />
        </template>
        <div>
          <ul
            :class="{
              'grid gap-3 md:grid-cols-3 md:gap-x-10': countries.length === 1,
              'flex flex-col space-y-2': countries.length > 1,
            }"
          >
            <template v-for="(usableCountry, item) in country.countries">
              <li
                v-if="item < 6 || viewAll[index] === true"
                :key="usableCountry.code"
                class="inline-flex items-center space-x-3 text-lg"
              >
                <div>
                  <img
                    :src="
                      usableCountry.code
                        ? `https://flagcdn.com/24x18/${usableCountry.code}.png`
                        : '/images/misc/flag-fallback.png'
                    "
                    :alt="usableCountry.country"
                  />
                </div>
                <span v-text="usableCountry.country" />
              </li>
            </template>
            <li
              v-if="countries.length === 1 && country.countries.length > 6"
              class="col-span-1"
            />
            <li
              v-if="country.countries.length > 6"
              class="mt-3"
              :class="{
                'md:col-start-2': countries.length === 1,
              }"
            >
              <CoeliacButton
                :label="`${viewAll[index] ? 'Hide' : 'Show'} ${country.countries.length - 6} more...`"
                size="lg"
                as="button"
                classes="w-full !justify-center !text-xl"
                bold
                @click="viewAll[index] = !viewAll[index]"
              />
            </li>
          </ul>
        </div>
      </Card>
    </div>
  </div>
</template>
