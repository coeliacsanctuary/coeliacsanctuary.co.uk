<script lang="ts" setup>
import {
  ProductQuantitySwitcherPropDefaults,
  ProductQuantitySwitcherProps,
} from '@/Components/Forms/Props';
import { defineModel } from 'vue';
import { MinusCircleIcon, PlusCircleIcon } from '@heroicons/vue/20/solid';

const props = withDefaults(
  defineProps<ProductQuantitySwitcherProps>(),
  ProductQuantitySwitcherPropDefaults,
);

const value = defineModel<number>();

const classes = (): string[] => {
  const base = [
    'flex-1',
    'w-full',
    'min-w-0',
    'appearance-none',
    'rounded-md',
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
    'text-base',
    'md:text-lg',
    'px-[calc(--spacing(4)-1px)]',
    'py-[calc(var(--spacing-1_75)-1px)]',
    'border',
    'border-grey-off',
    'focus:border-grey-dark',
    'shadow-xs',
    'bg-white',
    'h-full',
    '[appearance:textfield]',
    '[&::-webkit-outer-spin-button]:appearance-none',
    '[&::-webkit-inner-spin-button]:appearance-none',
  ];

  if (props.error) {
    base.push('border-red!', 'focus:border-red-dark');
  }

  base.push(props.inputClasses);

  return base;
};

const onlyAllowDigits = (e: KeyboardEvent) => {
  const allowedKeys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'Tab', 'Delete'];
  const isDigit = /^[0-9]$/.test(e.key);

  if (!isDigit && !allowedKeys.includes(e.key)) {
    e.preventDefault();
  }
};
</script>

<template>
  <div>
    <label
      class="block text-base leading-6 font-semibold text-primary-dark sm:max-xl:text-lg xl:text-xl"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red"
        v-text="'*'"
      />
    </label>

    <div
      class="relative h-[55px] rounded-md shadow-xs"
      :class="wrapperClasses"
    >
      <input
        v-model="value"
        :class="classes()"
        :name="name"
        required
        type="number"
        :min="min"
        :max="max"
        :disabled="disabled"
        @keydown="onlyAllowDigits"
      />

      <div
        v-if="value"
        class="absolute inset-y-0 right-0 py-1 pr-3"
      >
        <PlusCircleIcon
          class="size-6 cursor-pointer transition"
          :class="{
            'text-primary/30': value >= props.max || disabled,
            'text-primary hover:text-primary-dark':
              (!props.max || value < props.max) && !disabled,
            '!cursor-not-allowed': disabled,
          }"
          @click="
            (!props.max || value < props.max) && !disabled ? value++ : undefined
          "
        />

        <MinusCircleIcon
          class="size-6 cursor-pointer transition"
          :class="{
            'text-primary/30': value <= 1 || disabled,
            'text-primary hover:text-primary-dark': value > 1 && !disabled,
            '!cursor-not-allowed': disabled,
          }"
          @click="value > 1 && !disabled ? value-- : undefined"
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
  </div>
</template>
