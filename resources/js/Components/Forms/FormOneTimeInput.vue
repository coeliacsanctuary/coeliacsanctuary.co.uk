<script lang="ts" setup>
import {
  OneTimeInputPropDefaults,
  OneTimeInputProps,
} from '@/Components/Forms/Props';
import { ComponentPublicInstance, ref } from 'vue';
import { watchDeep } from '@vueuse/core';

const props = withDefaults(
  defineProps<OneTimeInputProps>(),
  OneTimeInputPropDefaults,
);

const [value] = defineModel<string, string>();

const inputs = ref(Array.from({ length: props.length }, () => ''));

const elements: (HTMLInputElement | null)[] = [];

const setElement = (
  el: Element | ComponentPublicInstance | null,
  index: number,
): void => {
  elements[index] = el as HTMLInputElement | null;
};

watchDeep(inputs, (newValue) => {
  value.value = newValue.join('');
});

const handlePaste = (e: ClipboardEvent, index: number): void => {
  if (index > 0) {
    return;
  }

  const pastedData = e.clipboardData?.getData('text');

  if (pastedData) {
    if (isNaN(parseInt(pastedData, 10))) {
      return;
    }

    inputs.value.forEach((input, index) => {
      inputs.value[index] = pastedData[index];
    });

    inputs.value.forEach((input, index) => {
      if (input !== '') {
        elements[index]?.focus();
      }
    });
  }
};

const handleKeyUp = (index: number): void => {
  if (inputs.value[index] !== '') {
    if (inputs.value[index].length > 1) {
      inputs.value[index] = inputs.value[index].slice(0, 1);
    }

    if (index < inputs.value.length - 1) {
      elements[index + 1]?.focus();
    }
  }
};

const handleDelete = (index: number): void => {
  if (inputs.value[index] === '') {
    if (index > 0) {
      inputs.value[index - 1] = '';
      elements[index - 1]?.focus();
    }
  }
};

const classes = (): string[] => {
  const base = [
    'flex-1',
    'w-8',
    'h-8',
    'xs:w-11',
    'xs:h-11',
    'text-lg',
    'sm:text-xl',
    'appearance-none',
    'rounded-md',
    'text-gray-900',
    'placeholder-gray-400',
    'outline-hidden',
    'focus:ring-0',
    'focus:outline-hidden',
    'transition',
    'disabled:text-gray-300',
    'disabled:cursor-not-allowed',
    '[&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
    'p-0',
  ];

  if (props.borders) {
    base.push('border border-grey-off focus:border-grey-dark shadow-xs');
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

    if (!props.borders && props.background && !props.hideErrorBackground) {
      base.push('bg-red/90!');
    }
  }

  base.push(props.inputClasses);

  return base;
};
</script>

<template>
  <div class="mx-auto flex flex-nowrap space-x-2 sm:space-x-4">
    <div
      v-for="(i, index) in inputs"
      :key="index"
      class="relative rounded-md"
      :class="[{ 'shadow-xs': borders }, wrapperClasses]"
    >
      <input
        :ref="(el) => setElement(el, index)"
        v-model="inputs[index]"
        :class="classes()"
        :name="name"
        :required="required"
        type="number"
        maxlength="1"
        @paste.prevent="handlePaste($event, index)"
        @keyup="() => handleKeyUp(index)"
        @keyup.delete="() => handleDelete(index)"
      />
    </div>
  </div>

  <p
    v-if="error"
    :id="`${name}-error`"
    class="mt-2 text-sm text-red"
    :class="errorClasses"
    v-text="error"
  />
</template>
