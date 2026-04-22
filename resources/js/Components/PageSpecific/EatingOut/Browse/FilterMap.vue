<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { AdjustmentsHorizontalIcon } from '@heroicons/vue/24/solid';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import TownFilterSidebarContent from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebarContent.vue';
import axios, { AxiosResponse } from 'axios';
import {
  EateryFilterItem,
  EateryFilterKeys,
  EateryFilters,
} from '@/types/EateryTypes';
import { DataResponse } from '@/types/GenericTypes';

const props = defineProps<{
  setFilters: Partial<{ [T in EateryFilterKeys]?: string[] }>;
}>();

const viewSidebar = ref(false);
const filters = ref<EateryFilters>();
const hasEmitted = ref(false);

const emits = defineEmits(['filtersUpdated']);

const getFilters = () => {
  axios
    .get('/api/wheretoeat/features')
    .then((response: AxiosResponse<DataResponse<EateryFilters>>) => {
      const defaultFilters = response.data.data;

      if (props.setFilters) {
        const keys: EateryFilterKeys[] = [
          'categories',
          'venueTypes',
          'features',
        ];

        keys.forEach((key) => {
          props.setFilters[key]?.forEach((category: string) => {
            const index = defaultFilters[key].indexOf(
              defaultFilters[key].find(
                (filter) => filter.value === category,
              ) as EateryFilterItem,
            );

            defaultFilters[key][index].checked = true;
          });
        });
      }

      filters.value = defaultFilters;

      if (hasEmitted.value) {
        emits('filtersUpdated', { filters: filters.value });
      }

      hasEmitted.value = true;
    });
};

const numberOfSetFilters = computed<number>(() => {
  let total = 0;

  if (!props.setFilters) {
    return total;
  }

  const keys: EateryFilterKeys[] = ['categories', 'venueTypes', 'features'];

  keys.forEach((key) => {
    total += props.setFilters[key]?.length;
  });

  return total;
});

onMounted(() => {
  if (filters.value) {
    return;
  }

  getFilters();
});
</script>

<template>
  <div
    v-show="filters"
    class="group absolute right-0 bottom-0 z-10 flex flex-row-reverse items-center"
  >
    <div
      class="relative m-4 ml-1 cursor-pointer rounded-full border-2 border-white bg-secondary p-3 text-white shadow-sm transition md:shadow-lg"
    >
      <div
        v-if="numberOfSetFilters > 0"
        class="absolute top-[-0.75rem] left-[-0.75rem] flex size-8 items-center justify-center rounded-full border-2 border-secondary bg-white leading-none font-semibold text-secondary"
        v-text="numberOfSetFilters"
      />

      <AdjustmentsHorizontalIcon
        class="h-8 w-8 md:max-xmd:h-12 md:max-xmd:w-12 xmd:h-14 xmd:w-14"
        @click="viewSidebar = true"
      />
    </div>

    <div
      class="pointer-events-none absolute right-[5.3rem] rounded-full border-2 border-white bg-secondary px-4 py-1 text-sm leading-none font-semibold uppercase opacity-0 transition-all duration-300 group-hover:opacity-70 group-hover:delay-500 md:max-xmd:right-[6.5rem] xmd:right-[7rem] xmd:text-base"
    >
      Filter
    </div>
  </div>

  <Sidebar
    :open="viewSidebar"
    side="right"
    @close="viewSidebar = false"
  >
    <TownFilterSidebarContent
      :filters="filters as EateryFilters"
      :number-of-filters="numberOfSetFilters"
      @updated="$emit('filtersUpdated', $event)"
    />
  </Sidebar>
</template>
