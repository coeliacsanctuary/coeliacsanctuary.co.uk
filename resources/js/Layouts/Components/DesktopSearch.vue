<script setup lang="ts">
import FormInput from '@/Components/Forms/FormInput.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import useSearch from '@/composables/useSearch';
import eventBus from '@/eventBus';

const { hasError, searchForm, submitSearch } = useSearch();

const processSearch = () => {
  eventBus.$emit('show-site-loader');

  submitSearch();
};
</script>

<template>
  <form
    class="mt-8 hidden flex-col items-center space-y-2 pr-2 transition md:flex"
    @submit.prevent="processSearch()"
  >
    <div
      class="flex w-full rounded-xl bg-white/50 pr-2 focus-within:bg-white/90"
    >
      <FormInput
        v-model="searchForm.q"
        label=""
        type="search"
        name="q"
        :background="false"
        placeholder="Search..."
        class="flex-1"
        hide-label
      />

      <button>
        <MagnifyingGlassIcon class="h-6 w-6" />
      </button>
    </div>

    <p
      v-if="hasError"
      class="text-right leading-none font-semibold text-red"
    >
      Please enter at least 3 characters to search!
    </p>
  </form>
</template>
