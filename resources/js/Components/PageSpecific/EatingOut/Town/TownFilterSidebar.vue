<script lang="ts" setup>
import { AdjustmentsHorizontalIcon } from '@heroicons/vue/24/solid';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { ref } from 'vue';
import TownFilterSidebarContent from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebarContent.vue';
import useScreensize from '@/composables/useScreensize';
import { EateryFilters } from '@/types/EateryTypes';

const props = defineProps<{
  filters: EateryFilters;
}>();
const viewSidebar = ref(false);

const { screenIsGreaterThanOrEqualTo } = useScreensize();

defineEmits(['filtersUpdated', 'sidebarClosed']);
</script>

<template>
  <div class="fixed right-0 bottom-0 z-10 p-4 xmd:hidden">
    <div
      class="-ml-3 rounded-full border-2 border-white bg-primary p-3 text-white shadow-sm transition"
    >
      <AdjustmentsHorizontalIcon
        class="h-8 w-8"
        @click="viewSidebar = true"
      />
    </div>
  </div>

  <div v-if="screenIsGreaterThanOrEqualTo('xmd')">
    <TownFilterSidebarContent
      :filters="filters"
      @updated="$emit('filtersUpdated', $event)"
    />
  </div>

  <Sidebar
    v-else
    :open="viewSidebar"
    side="right"
    @close="
      viewSidebar = false;
      $emit('sidebarClosed');
    "
  >
    <TownFilterSidebarContent
      :filters="filters"
      @updated="$emit('filtersUpdated', $event)"
    />
  </Sidebar>
</template>
