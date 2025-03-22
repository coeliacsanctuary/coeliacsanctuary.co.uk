<script lang="ts" setup>
import { TextareaProps, TextareaPropsDefaults } from '@/Components/Forms/Props';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';

const props = withDefaults(defineProps<TextareaProps>(), TextareaPropsDefaults);

const value = defineModel<string>();

const classes = (): string[] => {
  const base = [
    'flex-1',
    'w-full',
    'min-w-0',
    'appearance-none',
    'rounded-md',
    'px-[calc(--spacing(3)-1px)]',
    'py-[calc(--spacing(1.5)-1px)]',
    'text-base',
    'leading-7',
    'text-gray-900',
    'placeholder-gray-400',
    'shadow-xs',
    'outline-hidden',
    'sm:text-sm',
    'sm:leading-6',
    'xl:w-full',
    'focus:ring-0',
    'focus:outline-hidden transition',
  ];

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

  return base;
};
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
    <div class="relative rounded-md shadow-xs">
      <textarea
        v-model="value"
        :class="classes()"
        :name="name"
        :required="required"
        :rows="rows"
        v-bind="{
          ...(id ? { id } : null),
          ...(autocomplete ? { autocomplete } : null),
          ...(placeholder ? { placeholder } : null),
          ...(max ? { maxLength: max } : null),
        }"
      />

      <div
        v-if="error"
        class="pointer-events-none absolute inset-y-0 right-0 flex pr-3 pt-3"
      >
        <ExclamationCircleIcon
          aria-hidden="true"
          class="h-5 w-5 text-red"
        />
      </div>
    </div>

    <p
      v-if="helpText"
      class="text-sm"
      v-html="helpText"
    />

    <p
      v-if="error"
      :id="`${name}-error`"
      class="mt-2 text-sm text-red"
      v-text="error"
    />
  </div>
</template>
