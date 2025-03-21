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
    class="my-auto bg-transparent! max-w-[500px]"
    @close="emit('close')"
  >
    <form
      class="flex items-center flex-col space-y-2"
      @submit.prevent="
        submitSearch();
        $emit('close');
      "
    >
      <div class="flex items-center pr-2 w-full bg-white rounded-lg">
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
        class="text-red font-semibold text-center"
      >
        Please enter at least 3 characters to search!
      </p>
    </form>
  </OverlayFrame>
</template>
