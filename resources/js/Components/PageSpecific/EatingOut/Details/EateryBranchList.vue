<script lang="ts" setup>
import { DetailedEatery, EateryBranchesCollection } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Warning from '@/Components/Warning.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import BranchDisclosure from '@/Components/PageSpecific/EatingOut/Details/BranchList/BranchDisclosure.vue';
import BranchList from '@/Components/PageSpecific/EatingOut/Details/BranchList/BranchList.vue';
import { MapPinIcon } from '@heroicons/vue/24/solid';
import { Deferred } from '@inertiajs/vue3';
import { computed, nextTick, ref, Ref } from 'vue';
import { distanceInMiles, pluralise } from '@/helpers';
import useGeolocation from '@/composables/useGeolocation';
import {
  countBranches,
  filterBranches,
  flattenBranches,
  LocatedBranch,
} from '@/support/eating-out/branches';

const props = defineProps<{
  eatery: DetailedEatery;
  /** Deferred, so absent until the follow up request lands. */
  branches?: EateryBranchesCollection;
}>();

const allBranches = computed(() => props.branches ?? {});

const searchTerm = ref('');

const isSearching = computed(() => searchTerm.value.trim().length > 0);

const filteredBranches = computed(() =>
  filterBranches(allBranches.value, searchTerm.value),
);

const matchCount = computed(() => countBranches(filteredBranches.value));

const { isLocating, errorMessage, locate } = useGeolocation();

const nearest: Ref<LocatedBranch | undefined> = ref();
const nearestDistance = ref<number | undefined>();

const findNearest = async (): Promise<void> => {
  const coords = await locate();

  if (!coords) {
    nearest.value = undefined;

    return;
  }

  // A search would prune the tree out from under the branch we're about to open.
  searchTerm.value = '';

  let closest: LocatedBranch | undefined;
  let closestDistance = Infinity;

  flattenBranches(allBranches.value).forEach((entry) => {
    const distance = distanceInMiles(coords, {
      lat: Number(entry.branch.location.lat),
      lng: Number(entry.branch.location.lng),
    });

    if (distance < closestDistance) {
      closest = entry;
      closestDistance = distance;
    }
  });

  nearest.value = closest;
  nearestDistance.value = closestDistance;

  if (!closest) {
    return;
  }

  // The tree keeps its full height when locating, so the opened branch is often
  // well below the fold. Wait for the disclosures to render before scrolling.
  await nextTick();

  document
    .getElementById(`branch-${closest.branch.id}`)
    ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const isOnNearestPath = (
  country: string,
  county?: string,
  town?: string,
  area?: string,
): boolean => {
  if (!nearest.value || nearest.value.country !== country) {
    return false;
  }

  if (county !== undefined && nearest.value.county !== county) {
    return false;
  }

  if (town !== undefined && nearest.value.town !== town) {
    return false;
  }

  return area === undefined || nearest.value.area === area;
};

/** Only worth opening a branch outright once the search has narrowed to it. */
const isOnlyMatch = computed(() => isSearching.value && matchCount.value === 1);
</script>

<template>
  <div>
    <Deferred data="branches">
      <template #fallback>
        <Card class="flex flex-col space-y-4">
          <SubHeading
            text-size="small"
            class="pb-2"
          >
            Where to find {{ eatery.name }}
          </SubHeading>

          <p class="prose prose-sm max-w-none">
            Our gluten free eating out guide currently features
            <strong v-text="eatery.branch_count" />
            {{ eatery.name }}
            {{ pluralise('restaurant', eatery.branch_count) }} across the UK.
          </p>

          <div class="grid animate-pulse gap-2">
            <div
              v-for="placeholder in 5"
              :key="placeholder"
              class="h-10 rounded-sm bg-primary-light/30"
            />
          </div>
        </Card>
      </template>

      <Card class="flex flex-col space-y-4">
        <SubHeading
          text-size="small"
          class="pb-2"
        >
          Where to find {{ eatery.name }}
        </SubHeading>

        <p class="prose prose-sm max-w-none">
          Our gluten free eating out guide currently features
          <strong v-text="eatery.branch_count" />
          {{ eatery.name }}
          {{ pluralise('restaurant', eatery.branch_count) }} across the UK.
        </p>

        <FormInput
          v-model="searchTerm"
          name="branch-search"
          type="search"
          label="Search branches"
          placeholder="Search by town or postcode..."
          hide-label
          borders
        />

        <div class="flex flex-col space-y-2">
          <CoeliacButton
            as="button"
            type="button"
            theme="light"
            size="md"
            bold
            label="Find my nearest branch"
            :icon="MapPinIcon"
            :loading="isLocating"
            classes="justify-center"
            @click="findNearest()"
          />

          <p
            v-if="errorMessage()"
            class="text-xs text-red-dark"
            v-text="errorMessage()"
          />

          <p
            v-else-if="nearest && nearestDistance !== undefined"
            class="text-xs font-semibold text-grey-darker"
          >
            Your nearest branch is
            <strong>{{ nearest.branch.name || eatery.name }}</strong> in
            {{ nearest.town }}, {{ nearestDistance.toFixed(1) }} miles away.
          </p>
        </div>

        <p
          v-if="isSearching"
          class="text-xs font-semibold text-grey-darker"
        >
          {{ matchCount }} {{ pluralise('match', matchCount) }} for "{{
            searchTerm
          }}"
        </p>

        <Warning v-else>
          <p class="prose-sm max-w-none">
            Branches can open and close at any time without warning, please
            check
            {{ eatery.name }}'s website for the most accurate information.
          </p>
        </Warning>

        <p
          v-if="isSearching && matchCount === 0"
          class="py-4 text-center text-sm text-grey-dark italic"
        >
          No branches match "{{ searchTerm }}".
        </p>

        <div class="flex flex-col space-y-3">
          <BranchDisclosure
            v-for="(counties, country) in filteredBranches"
            :key="country"
            :label="<string>country"
            :count="countBranches(counties)"
            :force-open="isSearching || isOnNearestPath(<string>country)"
          >
            <BranchDisclosure
              v-for="(towns, county) in counties"
              :key="county"
              plain
              :label="<string>county"
              :count="countBranches(towns)"
              :force-open="
                isSearching || isOnNearestPath(<string>country, <string>county)
              "
            >
              <BranchDisclosure
                v-for="(areas, town) in towns"
                :key="town"
                :label="<string>town"
                :count="countBranches(areas)"
                :force-open="
                  isSearching ||
                  isOnNearestPath(<string>country, <string>county, <string>town)
                "
              >
                <template v-if="Object.keys(areas)[0] !== '_'">
                  <BranchDisclosure
                    v-for="(locations, area) in areas"
                    :key="area"
                    :label="<string>area"
                    :count="locations.length"
                    :force-open="
                      isSearching ||
                      isOnNearestPath(
                        <string>country,
                        <string>county,
                        <string>town,
                        <string>area,
                      )
                    "
                  >
                    <BranchList
                      v-for="branch in locations"
                      :key="branch.id"
                      :branch="branch"
                      :eatery-name="eatery.name"
                      :force-open="
                        isOnlyMatch || nearest?.branch.id === branch.id
                      "
                      :distance="
                        nearest?.branch.id === branch.id
                          ? nearestDistance
                          : undefined
                      "
                    />
                  </BranchDisclosure>
                </template>

                <template v-else>
                  <BranchList
                    v-for="branch in areas['_']"
                    :key="branch.id"
                    :branch="branch"
                    :eatery-name="eatery.name"
                    :force-open="
                      isOnlyMatch || nearest?.branch.id === branch.id
                    "
                    :distance="
                      nearest?.branch.id === branch.id
                        ? nearestDistance
                        : undefined
                    "
                  />
                </template>
              </BranchDisclosure>
            </BranchDisclosure>
          </BranchDisclosure>
        </div>
      </Card>
    </Deferred>
  </div>
</template>
