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

      emits('filtersUpdated', { filters: filters.value });
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
    class="group absolute bottom-0 right-0 z-10 p-4 md:p-6 select-none"
  >
    <div
      class="absolute left-0 ml-[-10px] rounded-full border-2 border-white bg-secondary px-4 py-1 text-sm font-semibold uppercase leading-none opacity-0 transition-all duration-300 group-hover:opacity-70 group-hover:delay-500 md:ml-[8px] xmd:ml-[10px] xmd:mt-[-38px] xmd:text-base"
      :class="numberOfSetFilters > 0 ? 'mt-[-43px]' : 'mt-[-28px]'"
    >
      Filter
    </div>

    <div
      class="relative -ml-3 cursor-pointer rounded-full border-2 border-white bg-secondary p-3 text-white shadow-sm transition md:shadow-lg"
    >
      <div
        v-if="numberOfSetFilters > 0"
        class="absolute top-[-0.75rem] left-[-0.75rem] bg-white text-secondary leading-none font-semibold size-8 flex justify-center items-center rounded-full border-2 border-secondary"
        v-text="numberOfSetFilters"
      />

      <AdjustmentsHorizontalIcon
        class="h-8 w-8 md:max-xmd:h-12 md:max-xmd:w-12 xmd:h-14 xmd:w-14"
        @click="viewSidebar = true"
      />
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
