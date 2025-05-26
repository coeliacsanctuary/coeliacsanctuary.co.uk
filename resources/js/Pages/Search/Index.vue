<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import useSearch from '@/composables/useSearch';
import { SearchParams, SearchResult } from '@/types/Search';
import { PaginatedResponse } from '@/types/GenericTypes';
import { nextTick, onMounted, Ref, ref, watch } from 'vue';
import { watchDebounced } from '@vueuse/core';
import Loader from '@/Components/Loader.vue';
import { Deferred, router } from '@inertiajs/vue3';
import eventBus from '@/eventBus';
import useBrowser from '@/composables/useBrowser';
import SearchResults from '@/Components/PageSpecific/Search/SearchResults.vue';
import { VisitOptions } from '@inertiajs/core';
import { LatLng } from '@/types/EateryTypes';

const props = defineProps<{
  parameters: SearchParams;
  location?: string;
  searchedLatLng?: LatLng;
  results?: PaginatedResponse<SearchResult>;
  hasEatery: boolean;
  aiAssisted: boolean;
}>();

const formParamsToSearchParams = (): URLSearchParams => {
  return new URLSearchParams({
    q: props.parameters.q,
    blogs: props.parameters.blogs ? 'true' : 'false',
    recipes: props.parameters.recipes ? 'true' : 'false',
    eateries: props.parameters.eateries ? 'true' : 'false',
    shop: props.parameters.shop ? 'true' : 'false',
  });
};

const landmark: Ref<Element> = ref();

const resultsElem: Ref<null | {
  reset: () => void;
  pause: boolean;
  refreshUrl: (url: string) => void;
  requestOptions: Partial<VisitOptions>;
}> = ref(null);

const { hasError, searchForm, latLng, cancelSearch, submitSearch } =
  useSearch();

onMounted(() => {
  if (resultsElem.value) {
    resultsElem.value.pause = true;
  }

  if (props.aiAssisted) {
    const url = new URL(useBrowser().currentUrl());
    url.search = formParamsToSearchParams().toString();

    nextTick(() => {
      router.get(
        url.toString(),
        {},
        {
          preserveScroll: true,
          preserveState: true,
          replace: true,
          onSuccess: () => {
            resultsElem.value?.refreshUrl(url.pathname + url.search);
          },
        },
      );
    });
  }

  if (latLng.value && resultsElem.value) {
    resultsElem.value.requestOptions = {
      headers: {
        'x-user-location': latLng.value,
      },
    };
  }

  nextTick(() => {
    if (resultsElem.value) {
      resultsElem.value.pause = false;
    }
  });
});

const stickyNav = ref(false);

eventBus.$on('sticky-nav-on', () => (stickyNav.value = true));
eventBus.$on('sticky-nav-off', () => (stickyNav.value = false));

searchForm.defaults(props.parameters).reset();

const shouldLoad = ref(true);

const handleSearch = () => {
  shouldLoad.value = true;

  if (resultsElem.value) {
    resultsElem.value.pause = true;
  }

  if (cancelSearch && cancelSearch.value) {
    (<Ref<{ cancel: () => void }>>cancelSearch).value.cancel();
  }

  nextTick(() => {
    submitSearch({
      onSuccess: () => {
        if (resultsElem.value) {
          resultsElem.value.pause = false;
          resultsElem.value.reset();
        }

        shouldLoad.value = false;
      },
    });
  });
};

watch(
  () => searchForm.blogs,
  () => handleSearch(),
);

watch(
  () => searchForm.recipes,
  () => handleSearch(),
);

watch(
  () => searchForm.eateries,
  () => handleSearch(),
);

watch(
  () => searchForm.shop,
  () => handleSearch(),
);

watch(
  () => latLng.value,
  () => {
    if (!latLng.value || !resultsElem.value) {
      return;
    }

    resultsElem.value.requestOptions = {
      headers: {
        'x-search-location': latLng.value,
      },
    };
  },
);

watchDebounced(
  () => searchForm.q,
  () => handleSearch(),
  { debounce: 500 },
);
</script>

<template>
  <div class="flex flex-col space-y-4 xmd:flex-row xmd:space-y-0 xmd:space-x-4">
    <div class="xmd:w-1/4 xmd:max-w-[215px] xmd:shrink-0">
      <Card
        class="mx-3 mt-3 rounded-lg bg-primary-light/40! xmd:max-w-[195px] xmd:rounded-lg xmd:border-2 xmd:border-primary xmd:bg-primary-light/10! xmd:p-3"
        :class="{
          'xmd:top-[40px]': stickyNav,
          'xmd:top-auto': !stickyNav,
          'xmd:fixed':
            !searchForm.processing &&
            !shouldLoad &&
            results &&
            results.data?.length > 1,
        }"
        faded
        :shadow="false"
      >
        <form
          class="flex flex-col space-y-4"
          @submit.prevent="undefined"
        >
          <FormInput
            v-model="searchForm.q"
            label=""
            type="search"
            name="q"
            placeholder="Search..."
            class="flex-1"
            hide-label
            borders
          />

          <p
            v-if="hasError"
            class="font-semibold break-words text-red"
          >
            Please enter at least 3 characters
          </p>

          <div class="grid grid-cols-2 gap-2 xmd:grid-cols-1">
            <FormCheckbox
              v-model="searchForm.blogs"
              label="Blogs"
              layout="left"
              name="blogs"
              xl
            />

            <FormCheckbox
              v-model="searchForm.recipes"
              label="Recipes"
              layout="left"
              name="recipes"
              xl
            />

            <FormCheckbox
              v-model="searchForm.eateries"
              label="Eateries"
              layout="left"
              name="eateries"
              xl
            />

            <FormCheckbox
              v-model="searchForm.shop"
              label="Shop"
              layout="left"
              name="shop"
              xl
            />
          </div>
        </form>
      </Card>
    </div>

    <Deferred data="results">
      <template #fallback>
        <Card class="mt-4! w-full">
          <Loader
            color="primary"
            :display="true"
            :absolute="false"
            size="size-12"
          />
        </Card>
      </template>

      <SearchResults
        v-if="results"
        ref="resultsElem"
        :should-load="shouldLoad"
        :results="results"
        :landmark="landmark"
        :has-eatery="hasEatery"
        :location="location"
        :search-lat-lng="searchedLatLng"
        :term="parameters.q"
        @mounted="shouldLoad = false"
      />
    </Deferred>

    <div ref="landmark" />
  </div>
</template>
