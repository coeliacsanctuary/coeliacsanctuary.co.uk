<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Warning from '@/Components/Warning.vue';
import { LondonBoroughPage } from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { computed, ref } from 'vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';

const props = defineProps<{ borough: LondonBoroughPage }>();

const areaList = ref<HTMLElement | null>(null);
const areaSearch = ref('');

const sortOptions = ref<FormSelectOption[]>([
  { label: 'Alphabetically', value: 'alphabetical' },
  { label: 'Total Eateries', value: 'eateries' },
]);

const currentSort = ref('alphabetical');

const filteredAreas = computed(() => {
  const areas = props.borough.areas.filter((area) =>
    area.name.toLowerCase().includes(areaSearch.value.toLowerCase()),
  );

  if (currentSort.value === 'eateries') {
    return [...areas].sort((a, b) => b.total_eateries - a.total_eateries);
  }

  return areas;
});
</script>

<template>
  <TownHeading
    :county="borough.county"
    :image="borough.image"
    :name="borough.name"
    :latlng="borough.latlng"
    london-borough
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <div
      class="prose-md prose max-w-none lg:!my-0 lg:prose-lg *:first:lg:mt-0"
      v-html="borough.intro_text"
    />

    <p class="prose-md prose max-w-none lg:prose-lg">
      The wealth of information in our guide is a result of the generous
      contributions from people like you - fellow Coeliacs or individuals with
      gluten intolerance, who are familiar with their local area. These
      kind-hearted individuals take the time to share their knowledge and help
      us build a comprehensive list of places to eat to help others, like you!
    </p>

    <Warning>
      <p>
        While we take every care to make sure our eating out guide is accurate,
        places can change without notice, we always recommend that you check
        ahead before making plans.
      </p>

      <p class="mt-2">
        All eateries are recommended by our website visitors, and before going
        live we check menus and reviews, but we do not vet or visit places to
        independently check them.
      </p>
    </Warning>
  </Card>

  <div ref="areaList">
    <Card class="mx-4 mb-4">
      <div class="flex items-center justify-between">
        <FormInput
          v-model="areaSearch"
          name="search"
          label=""
          :placeholder="`Search for an area in ${borough.name}...`"
          hide-label
          borders
          class="w-full max-w-md"
        />

        <FormSelect
          v-model="currentSort"
          name="sort"
          :options="sortOptions"
          label="Sort by"
          borders
          class="flex items-center space-x-2 xs:flex-col xs:items-start xs:space-x-0 sm:flex-row sm:items-center sm:space-x-2"
          size="small"
        />
      </div>
    </Card>

    <div class="group grid gap-3 px-4 md:grid-cols-3">
      <CountyTown
        v-for="area in filteredAreas"
        :key="area.name"
        :town="area"
      />
    </div>
  </div>

  <JumpToContentButton
    v-if="areaList"
    :anchor="areaList"
    label="Jump to Area List"
  />
</template>
