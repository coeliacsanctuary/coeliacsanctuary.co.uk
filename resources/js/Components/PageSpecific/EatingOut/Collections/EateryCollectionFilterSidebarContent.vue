<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { CheckboxItem } from '@/types/Types';
import FormCheckboxGroup from '@/Components/Forms/FormCheckboxGroup.vue';
import { computed, ComputedRef, Ref, ref } from 'vue';
import { EateryCollectionFilters } from '@/types/EateryTypes';

const props = defineProps<{
  filters: EateryCollectionFilters;
}>();

const eateryTypeFilters: Ref<CheckboxItem[]> = ref(props.filters.categories);
const venueTypeFilters: Ref<CheckboxItem[]> = ref(props.filters.venueTypes);
const featureFilters: Ref<CheckboxItem[]> = ref(props.filters.features);

const townFilters: Ref<CheckboxItem[]> = ref(props.filters.towns);
const countyFilters: Ref<CheckboxItem[]> = ref(props.filters.counties);

const emits = defineEmits(['updated']);

const filtersChanged = (preserveState: boolean = true) => {
  emits('updated', {
    filters: {
      categories: eateryTypeFilters.value,
      venueTypes: venueTypeFilters.value,
      features: featureFilters.value,

      towns: townFilters.value,
      counties: countyFilters.value,
    },
    preserveState,
  });
};

const isFiltered: ComputedRef<boolean> = computed(() => {
  const filters = [
    ...eateryTypeFilters.value.map((filter) => filter.checked),
    ...venueTypeFilters.value.map((filter) => filter.checked),
    ...featureFilters.value.map((filter) => filter.checked),

    ...townFilters.value.map((filter) => filter.checked),
    ...countyFilters.value.map((filter) => filter.checked),
  ];

  return filters.includes(true);
});

const resetFilters = () => {
  eateryTypeFilters.value = eateryTypeFilters.value.map((filter) => ({
    ...filter,
    checked: false,
  }));

  venueTypeFilters.value = venueTypeFilters.value.map((filter) => ({
    ...filter,
    checked: false,
  }));

  featureFilters.value = featureFilters.value.map((filter) => ({
    ...filter,
    checked: false,
  }));

  townFilters.value = townFilters.value.map((filter) => ({
    ...filter,
    checked: false,
  }));

  countyFilters.value = countyFilters.value.map((filter) => ({
    ...filter,
    checked: false,
  }));

  filtersChanged(false);
};
</script>

<template>
  <Card class="flex h-auto flex-col space-y-3 p-0!">
    <div
      class="flex items-center justify-between border-b border-grey-off bg-grey-off-light p-3"
    >
      <div class="text-lg font-semibold">Filter Eateries</div>

      <a
        :class="
          isFiltered
            ? 'bg-primary-light/70 opacity-100 hover:opacity-100'
            : 'bg-primary-light opacity-0'
        "
        class="cursor-pointer rounded-lg px-2 py-1 shadow-sm transition"
        @click.prevent="resetFilters"
      >
        Reset
      </a>
    </div>

    <div
      v-if="countyFilters.length > 1"
      class="px-3"
    >
      <FormCheckboxGroup
        v-model="countyFilters"
        label="County"
        collapsible
        is-collapsed-by-default
        searchable
        @change="filtersChanged"
      />
    </div>

    <div
      v-if="townFilters.length > 1"
      class="px-3"
    >
      <FormCheckboxGroup
        v-model="townFilters"
        label="Town"
        collapsible
        is-collapsed-by-default
        searchable
        @change="filtersChanged"
      />
    </div>

    <div
      v-if="eateryTypeFilters.length > 1"
      class="px-3"
    >
      <FormCheckboxGroup
        v-model="eateryTypeFilters"
        label="Venue Category"
        @change="filtersChanged"
      />
    </div>

    <div
      v-if="venueTypeFilters.length > 1"
      class="px-3"
    >
      <FormCheckboxGroup
        v-model="venueTypeFilters"
        label="Venue Type"
        @change="filtersChanged"
      />
    </div>

    <div
      v-if="featureFilters.length > 1"
      class="px-3"
    >
      <FormCheckboxGroup
        v-model="featureFilters"
        label="Special Feature"
        @change="filtersChanged"
      />
    </div>
  </Card>
</template>
