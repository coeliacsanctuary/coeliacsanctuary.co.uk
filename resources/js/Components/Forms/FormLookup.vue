<script lang="ts" setup>
import {
  FormLookupPropDefaults,
  FormLookupProps,
} from '@/Components/Forms/Props';
import { ExclamationCircleIcon, XCircleIcon } from '@heroicons/vue/20/solid';
import { ref, watch } from 'vue';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';

const props = withDefaults(
  defineProps<FormLookupProps>(),
  FormLookupPropDefaults,
);

const emits = defineEmits(['search', 'unlock', 'typed']);

const value = ref(props.initialValue ?? '');

const results = ref<object[]>([]);

const showResultsBox = ref(false);

const highlightedIndex = ref(-1);

const suppressSearch = ref(false);

const classes = (): string[] => {
  const base = [
    'flex-1',
    'w-full',
    'min-w-0',
    'appearance-none',
    'leading-7',
    'text-gray-900',
    'placeholder-gray-400',
    'outline-hidden',
    'xl:w-full',
    'focus:ring-0',
    'focus:outline-hidden',
    'transition',
    'disabled:text-gray-300',
    'disabled:cursor-not-allowed',
  ];

  if (props.size === 'large') {
    base.push(
      'text-base md:text-lg px-[calc(--spacing(4)-1px)] py-[calc(var(--spacing-1_75)-1px)]',
    );
  } else {
    base.push(
      'px-[calc(--spacing(3)-1px)] py-[calc(--spacing(1.5)-1px)] text-base sm:text-sm sm:leading-6',
    );
  }

  if (props.borders) {
    base.push('border border-grey-off shadow-xs');
  } else {
    base.push('border-0');
  }

  if (props.background) {
    base.push('bg-white');
  } else {
    base.push('bg-transparent');
  }

  if (props.error) {
    base.push('border-red!', 'focus:border-red-dark');

    if (!props.borders && props.background) {
      base.push('bg-red/90!');
    }
  }

  base.push(showResultsBox.value ? 'rounded-t-md' : 'rounded-md');

  base.push(props.inputClasses);

  return base;
};

const performSearch = () => {
  if (suppressSearch.value) {
    suppressSearch.value = false;
    return;
  }

  if (value.value === '' || props.lock) {
    showResultsBox.value = false;
    return;
  }

  axios
    .post(props.lookupEndpoint, {
      [props.postParameter]: value.value,
    })
    .then((response) => {
      showResultsBox.value = true;
      highlightedIndex.value = -1;

      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      results.value = <object[]>response.data[props.resultKey];

      emits('search', results.value);
    });
};

const reset = () => {
  value.value = '';
  showResultsBox.value = false;
  results.value = [];
  highlightedIndex.value = -1;
};

const setValue = (newValue: string) => {
  suppressSearch.value = true;
  value.value = newValue;
  showResultsBox.value = false;
  results.value = [];
  highlightedIndex.value = -1;
};

const close = () => {
  showResultsBox.value = false;
  highlightedIndex.value = -1;
};

const resultCount = (): number => results.value.length + (props.allowAny ? 1 : 0);

const clickSlottedResult = (event: KeyboardEvent) => {
  const slottedResult = (event.currentTarget as HTMLElement).firstElementChild;

  if (slottedResult instanceof HTMLElement) {
    slottedResult.click();
  }
};

const move = (step: number) => {
  const total = resultCount();

  if (!showResultsBox.value || total === 0) {
    return;
  }

  highlightedIndex.value = (highlightedIndex.value + step + total) % total;

  document.getElementById(`${props.name}-result-${highlightedIndex.value}`)?.focus();
};

defineExpose({ reset, value, setValue, close });

watch(
  () => props.preselectTerm,
  () => {
    if (props.preselectTerm) {
      value.value = props.preselectTerm;
    }
  },
);

watchDebounced(value, performSearch, { debounce: 500 });
</script>

<template>
  <div>
    <label
      v-if="hideLabel === false"
      :for="id"
      class="block leading-6 font-semibold text-primary-dark"
      :class="
        size === 'large'
          ? 'text-base sm:max-xl:text-lg xl:text-xl'
          : 'text-base sm:text-lg'
      "
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red"
        v-text="'*'"
      />
    </label>

    <small
      v-if="helpText"
      class="mt-0 mb-2 block text-sm leading-none text-grey-dark"
      v-text="helpText"
    />

    <div
      class="relative rounded-md"
      :class="{
        'shadow-xs': borders,
        'rounded-md': showResultsBox === false,
        'rounded-t-md': showResultsBox,
      }"
    >
      <input
        v-model="value"
        :class="classes()"
        :name="name"
        :required="required"
        type="text"
        :readonly="lock"
        v-bind="{
          ...(id ? { id } : null),
          ...(autocomplete ? { autocomplete } : null),
          ...(placeholder ? { placeholder } : null),
          ...(disabled ? { disabled } : null),
          ...(min ? { min } : null),
          ...(max ? { max } : null),
        }"
        @keyup="$emit('typed')"
        @keydown.down.prevent="move(1)"
        @keydown.up.prevent="move(-1)"
        @keydown.esc.prevent="close()"
      />

      <div
        v-if="lock"
        class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3"
        @click="
          value = '';
          $emit('unlock');
        "
      >
        <XCircleIcon
          aria-hidden="true"
          class="h-5 w-5 text-grey-darkest transition hover:text-primary-dark"
        />
      </div>

      <div
        v-if="error"
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
      >
        <ExclamationCircleIcon
          aria-hidden="true"
          class="h-5 w-5 text-red"
        />
      </div>
    </div>

    <div
      v-if="showResultsBox && !lock"
      class="rounded-b-md border border-t-0 border-grey-off shadow-xs focus:border-grey-dark"
      :class="resultsClasses"
    >
      <ul
        v-if="results.length > 0 || allowAny"
        @keydown.down.prevent="move(1)"
        @keydown.up.prevent="move(-1)"
        @keydown.esc.prevent="close()"
      >
        <li
          v-for="(result, index) in results"
          :key="index"
        >
          <button
            :id="`${name}-result-${index}`"
            type="button"
            class="block w-full cursor-pointer text-left"
            @keydown.enter.prevent="clickSlottedResult"
            @focus="highlightedIndex = index"
          >
            <slot
              name="item"
              v-bind="result"
            />
          </button>
        </li>
        <li v-if="allowAny">
          <button
            :id="`${name}-result-${results.length}`"
            type="button"
            class="block w-full cursor-pointer text-left"
            @keydown.enter.prevent="clickSlottedResult"
            @focus="highlightedIndex = results.length"
          >
            <slot
              name="item"
              v-bind="{
                ...fallbackObject,
                [fallbackKey]: value,
              }"
            />
          </button>
        </li>
      </ul>

      <div
        v-else
        class="py-2 text-center"
      >
        <slot name="no-results">
          <em>Nothing found...</em>
        </slot>
      </div>
    </div>

    <p
      v-if="error"
      :id="`${name}-error`"
      class="mt-2 text-sm text-red"
      v-text="error"
    />
  </div>
</template>
