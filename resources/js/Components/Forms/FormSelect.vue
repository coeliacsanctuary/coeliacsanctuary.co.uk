<script lang="ts" setup>
import {
  FormSelectGroup,
  FormSelectOption,
  FormSelectProps,
  FormSelectPropsDefaults,
} from '@/Components/Forms/Props';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';

const props = withDefaults(
  defineProps<FormSelectProps>(),
  FormSelectPropsDefaults,
);

const value = defineModel<string | number | boolean>();

const classes = (): string[] => {
  const base = [
    'min-w-0',
    'appearance-none',
    'rounded-md',
    'leading-7',
    'text-gray-900',
    'placeholder-gray-400',
    'shadow-xs',
    'outline-hidden',
    'focus:ring-0',
    'focus:outline-hidden transition',
    'w-full',
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
    base.push('border border-grey-off focus:border-grey-dark');
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

  base.push(props.inputClasses);

  return base;
};
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
    <div class="relative rounded-md shadow-xs">
      <select
        v-model="value"
        :name="name"
        :class="classes()"
        :disabled="disabled"
        :required="required"
        v-bind="{
          ...(id ? { id } : null),
          ...(autocomplete ? { autocomplete } : null),
        }"
      >
        <option
          v-if="placeholder"
          value=""
          disabled
          class="text-grey"
          v-text="placeholder"
        />

        <template v-if="options[0].options">
          <optgroup
            v-for="group in <FormSelectGroup[]>options"
            :key="group.label"
            :label="group.label"
          >
            <option
              v-for="option in group.options"
              :key="option.value.toString()"
              :value="option.value"
              v-text="option.label"
            />
          </optgroup>
        </template>

        <template v-else>
          <option
            v-for="option in <FormSelectOption[]>options"
            :key="option.value.toString()"
            :value="option.value"
            v-text="option.label"
          />
        </template>
      </select>

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

    <p
      v-if="error"
      :id="`${name}-error`"
      class="mt-2 text-sm text-red"
      v-text="error"
    />
  </div>
</template>
