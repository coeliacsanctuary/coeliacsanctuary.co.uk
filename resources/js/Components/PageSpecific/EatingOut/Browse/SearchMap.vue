<script setup lang="ts">
import FormLookup from '@/Components/Forms/FormLookup.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { MapPinIcon } from '@heroicons/vue/24/solid';
import axios, { AxiosResponse } from 'axios';
import { LatLng } from '@/types/EateryTypes';

type LookupResult = { id: string; label: string };

const hasError = ref(false);
const errorMessage = ref('Please insert at least 3 characters...');
const sessionToken = ref(crypto.randomUUID());
const lookup = ref<null | { setValue: (value: string) => void; value: string }>(
  null,
);
const results = ref<LookupResult[]>([]);
const waitingForResults = ref(false);
const searchedPlace = ref<(LatLng & { label: string }) | null>(null);

const encodeDestination = (place: LatLng & { label: string }): string => {
  const bytes = new TextEncoder().encode(
    JSON.stringify({ label: place.label, lat: place.lat, lng: place.lng }),
  );

  return btoa(String.fromCharCode(...bytes))
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/, '');
};

const emits = defineEmits(['loading', 'end-loading', 'navigate-to']);

const goToPlace = (place: LookupResult) => {
  waitingForResults.value = false;
  hasError.value = false;

  lookup.value?.setValue(place.label);

  emits('loading');

  axios
    .get(`/api/wheretoeat/lookup/${encodeURIComponent(place.id)}`, {
      params: { sessionToken: sessionToken.value },
    })
    .then((response: AxiosResponse<LatLng>) => {
      sessionToken.value = crypto.randomUUID();
      searchedPlace.value = { ...response.data, label: place.label };

      emits('navigate-to', response.data);
    })
    .catch(() => {
      errorMessage.value = 'Location not found...';
      hasError.value = true;
      emits('end-loading');
    });
};

const handleResults = (newResults: LookupResult[]) => {
  results.value = newResults;

  if (!waitingForResults.value) {
    return;
  }

  if (!newResults.length) {
    waitingForResults.value = false;
    errorMessage.value = 'Location not found...';
    hasError.value = true;

    return;
  }

  goToPlace(newResults[0]);
};

const handleSearch = () => {
  if (!lookup.value || lookup.value.value.length < 3) {
    hasError.value = true;
    return;
  }

  hasError.value = false;

  if (results.value.length) {
    goToPlace(results.value[0]);

    return;
  }

  waitingForResults.value = true;
};

const getLocation = () => {
  searchedPlace.value = null;

  emits('loading');

  hasError.value = false;

  navigator.geolocation.getCurrentPosition(
    (position) => {
      emits('navigate-to', {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
      });
    },
    () => {
      errorMessage.value = 'Sorry, there was an error finding your location...';
      hasError.value = true;
      emits('end-loading');
    },
  );
};

watch(hasError, (check) => {
  if (check) {
    setTimeout(() => {
      hasError.value = false;
    }, 3000);
  }
});
</script>

<template>
  <div class="absolute z-10 w-[calc(100%-40px)] max-w-lg p-2">
    <div class="w-full max-w-lg">
      <form
        class="flex w-full max-w-lg items-center gap-2 rounded-md bg-white shadow-sm"
        :class="hasError ? 'border border-red' : ''"
        @submit.prevent="handleSearch()"
      >
        <FormLookup
          ref="lookup"
          label=""
          name="search"
          hide-label
          :borders="false"
          placeholder="Search for an address, town or postcode..."
          size="large"
          class="flex-1"
          results-classes="absolute bg-white w-full z-20"
          :lookup-endpoint="`/api/wheretoeat/lookup?sessionToken=${sessionToken}`"
          ignore-autofill
          @search="handleResults"
          @typed="results = []"
        >
          <template #item="place: LookupResult">
            <div
              class="cursor-pointer border-b border-grey-off p-2 transition hover:bg-grey-lightest"
              @click="goToPlace(place)"
              v-text="place.label"
            />
          </template>
        </FormLookup>

        <button
          class="flex size-6 items-center justify-center rounded-full"
          :class="hasError ? 'bg-red' : 'bg-primary'"
          @click.prevent="handleSearch()"
        >
          <MagnifyingGlassIcon class="size-4" />
        </button>

        <div class="h-8 w-[1px] bg-grey-off" />

        <button
          class="mr-1 flex size-6 items-center justify-center rounded-full bg-secondary"
          type="button"
          @click.prevent="getLocation()"
        >
          <MapPinIcon
            class="size-4 opacity-50 transition-all hover:opacity-75"
          />
        </button>
      </form>
    </div>

    <div
      v-if="searchedPlace"
      class="mt-2 flex w-full max-w-lg items-center gap-3 rounded-md bg-white p-3 shadow-sm"
    >
      <p class="flex-1">
        Heading to
        <strong v-text="searchedPlace.label.split(',')[0]" />? Plan a gluten
        free trip there.
      </p>

      <CoeliacButton
        :href="`/wheretoeat/trip-planner?to=${encodeDestination(searchedPlace)}`"
        theme="primary"
        size="lg"
        bold
        label="Plan your trip"
        classes="shrink-0"
        @click="
          useJourneyTracking().logEvent('clicked', 'WhereToEatMap/PlanTripCta', {
            title: searchedPlace.label,
          })
        "
      />

      <button
        type="button"
        class="shrink-0 cursor-pointer rounded-md p-1 text-grey-dark hover:bg-grey-light"
        aria-label="Dismiss"
        @click="searchedPlace = null"
      >
        <XMarkIcon class="size-5" />
      </button>
    </div>

    <div
      class="mt-1 w-[calc(100%-35px)] max-w-lg rounded-md bg-red/70 p-1 text-sm leading-none font-semibold text-white shadow-sm transition-all"
      :class="hasError ? 'opacity-100' : 'opacity-0'"
      v-text="errorMessage"
    />
  </div>
</template>
