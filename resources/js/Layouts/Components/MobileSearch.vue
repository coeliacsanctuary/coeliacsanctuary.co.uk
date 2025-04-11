<script setup lang="ts">
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import FormInput from '@/Components/Forms/FormInput.vue';
import { nextTick, watch } from 'vue';
import OverlayFrame from '@/Components/Overlays/OverlayFrame.vue';
import useSearch from '@/composables/useSearch';

const props = defineProps<{ open: boolean }>();

const emit = defineEmits(['close']);

const { hasError, searchForm, submitSearch } = useSearch();

watch(
  () => props.open,
  () => {
    hasError.value = false;

    if (props.open && typeof document !== 'undefined') {
      nextTick(() => {
        (<HTMLInputElement>document.getElementById('mobileSearch')).focus();
      });
    }
  },
);
</script>

<template>
  <OverlayFrame
    :open="open"
    width="w-full"
    class="my-auto max-w-[500px] bg-transparent!"
    @close="emit('close')"
  >
    <form
      class="flex flex-col items-center space-y-2"
      @submit.prevent="
        submitSearch();
        $emit('close');
      "
    >
      <div class="flex w-full items-center rounded-lg bg-white pr-2">
        <FormInput
          id="mobileSearch"
          v-model="searchForm.q"
          label=""
          type="search"
          name="q"
          :background="false"
          placeholder="Search..."
          class="flex-1"
          hide-label
          size="large"
          input-classes="text-xl!  p-2!"
        />

        <button>
          <MagnifyingGlassIcon class="h-6 w-6" />
        </button>
      </div>

      <p
        v-if="hasError"
        class="text-center font-semibold text-red"
      >
        Please enter at least 3 characters to search!
      </p>
    </form>
  </OverlayFrame>
</template>
