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

const emits = defineEmits(['search', 'unlock']);

const value = ref('');

const results = ref<object[]>([]);

const showResultsBox = ref(false);

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

      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      results.value = <object[]>response.data[props.resultKey];

      emits('search', results.value);
    });
};

const reset = () => {
  value.value = '';
  showResultsBox.value = false;
  results.value = [];
};

defineExpose({ reset });

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
      class="block font-semibold leading-6 text-primary-dark"
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
      class="mb-2 mt-0 block text-sm leading-none text-grey-dark"
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
      />

      <div
        v-if="lock"
        class="cursor-pointer absolute inset-y-0 right-0 flex items-center pr-3"
        @click="
          value = '';
          $emit('unlock');
        "
      >
        <XCircleIcon
          aria-hidden="true"
          class="h-5 w-5 text-grey-darkest hover:text-primary-dark transition"
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
      class="rounded-b-md border border-grey-off focus:border-grey-dark shadow-xs border-t-0"
    >
      <ul v-if="results.length > 0 || allowAny">
        <li
          v-for="(result, index) in results"
          :key="index"
        >
          <slot
            name="item"
            v-bind="result"
          />
        </li>
        <li v-if="allowAny">
          <slot
            name="item"
            v-bind="{
              ...fallbackObject,
              [fallbackKey]: value,
            }"
          />
        </li>
      </ul>

      <div
        v-else
        class="text-center py-2"
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
