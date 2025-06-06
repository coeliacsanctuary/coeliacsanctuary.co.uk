<script setup lang="ts">
import Card from '@/Components/Card.vue';
import FormLookup from '@/Components/Forms/FormLookup.vue';
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';
import Loader from '@/Components/Loader.vue';
import { ShopProductIndex } from '@/types/Shop';
import { nextTick, onMounted, ref } from 'vue';
import axios, { AxiosResponse } from 'axios';
import useBrowser from '@/composables/useBrowser';
import Heading from '@/Components/Heading.vue';
import { router } from '@inertiajs/vue3';
import useUrl from '@/composables/useUrl';

type SearchResult = {
  term: string;
  type: string;
  products: ShopProductIndex[];
};

const lookup = ref<null | { reset: () => void; value: string }>(null);

const loadingResult = ref(false);
const searchResult = ref<SearchResult | null>(null);

const { currentUrl } = useBrowser();

const selectResult = (id: number) => {
  nextTick(() => {
    loadingResult.value = true;

    axios
      .get(`/api/shop/travel-card-search/${id}`)
      .then((response: AxiosResponse<SearchResult>) => {
        const url = new URL(useUrl().currentUrl());
        url.searchParams.set('term', lookup.value?.value);

        router.get(
          url.toString(),
          {},
          {
            preserveState: true,
            preserveScroll: true,
          },
        );

        // eslint-disable-next-line @typescript-eslint/no-unsafe-call
        lookup.value?.reset();
        searchResult.value = response.data;
        loadingResult.value = false;
      });
  });
};

const termFromSearch = ref();
const hasTyped = ref(false);

const handleSearch = (results: { id: number }[]) => {
  if (!termFromSearch.value || hasTyped.value) {
    return;
  }

  selectResult(results[0].id);
};

const typed = () => {
  hasTyped.value = true;
};

const searchContainer = ref<null | HTMLElement>(null);

onMounted(() => {
  nextTick(() => {
    const url = new URL(currentUrl());
    if (url && url.searchParams && url.searchParams.has('term')) {
      setTimeout(() => {
        searchContainer.value?.scrollIntoView({
          behavior: 'smooth',
          inline: 'start',
        });

        termFromSearch.value = url.searchParams.get('term');
      }, 200);
    }
  });
});
</script>

<template>
  <Card
    class="flex items-center justify-center"
    theme="primary"
    faded
  >
    <div
      ref="searchContainer"
      class="flex w-full flex-col items-center space-y-4 sm:w-2/3"
    >
      <Heading :border="false">Where are you heading?</Heading>

      <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
        Enter the country or language below and we'll try and find the best
        travel card for you!
      </p>

      <FormLookup
        ref="lookup"
        label=""
        name=""
        placeholder="Search for country or language"
        size="large"
        hide-label
        borders
        class="w-full"
        lookup-endpoint="/api/shop/travel-card-search"
        :preselect-term="termFromSearch"
        input-classes="text-2xl! p-4! text-center"
        results-classes="bg-white"
        @search="handleSearch"
        @typed="typed"
      >
        <template #item="{ id, term, type }">
          <div
            class="flex cursor-pointer space-x-2 border-b border-grey-off bg-grey-light text-left transition hover:bg-grey-lightest"
            @click="selectResult(id)"
          >
            <span
              class="flex-1 p-2"
              v-html="term"
            />
            <span
              class="flex w-[77px] items-center justify-center bg-grey-off-light text-xs font-semibold text-grey-dark sm:w-[100px]"
            >
              {{ type.charAt(0).toUpperCase() + type.slice(1) }}
            </span>
          </div>
        </template>

        <template #no-results>
          <div class="flex flex-col space-y-2 p-3 text-center">
            <div>Sorry, nothing found</div>

            <div>
              Make sure you're searching for a country or a language, and not a
              city or place name, so search <strong>France</strong>, not
              <strong>Paris</strong> for example!
            </div>
          </div>
        </template>
      </FormLookup>
    </div>
  </Card>

  <template v-if="searchResult">
    <div
      v-if="loadingResult"
      class="relative min-h-map w-full items-center justify-center"
    >
      <Loader :display="true" />
    </div>

    <template v-else>
      <Card>
        <Heading
          v-if="searchResult.type === 'country'"
          :border="false"
        >
          Here are our travel cards that can be used in
          <span class="text-primary-dark">{{ searchResult.term }}</span>
        </Heading>

        <Heading
          v-else
          :border="false"
        >
          Here are our travel cards that can be used in
          <span class="text-primary-dark">{{ searchResult.term }}</span>
          speaking areas
        </Heading>
      </Card>

      <div class="grid gap-4 sm:max-lg:grid-cols-2 lg:grid-cols-3">
        <CategoryProductCard
          v-for="product in searchResult.products"
          :key="product.id"
          :product="product"
        />
      </div>
    </template>
  </template>
</template>

<style scoped></style>
