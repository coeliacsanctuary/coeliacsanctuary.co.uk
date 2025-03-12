<script lang="ts" setup>
import {
  ProductQuantitySwitcherPropDefaults,
  ProductQuantitySwitcherProps,
} from '@/Components/Forms/Props';
import { defineModel } from 'vue';
import { PlusCircleIcon, MinusCircleIcon } from '@heroicons/vue/20/solid';

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
</script>

<template>
  <div>
    <label
      class="block font-semibold leading-6 text-primary-dark text-base sm:max-xl:text-lg xl:text-xl"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red"
        v-text="'*'"
      />
    </label>

    <div
      class="relative rounded-md shadow-xs h-[55px]"
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
      />

      <div class="absolute inset-y-0 right-0 py-1 pr-3">
        <PlusCircleIcon
          class="size-6 transition cursor-pointer"
          :class="{
            'text-primary/30': value >= props.max,
            'text-primary hover:text-primary-dark':
              !props.max || value < props.max,
          }"
          @click="!props.max || value < props.max ? value++ : undefined"
        />

        <MinusCircleIcon
          class="size-6 transition cursor-pointer"
          :class="{
            'text-primary/30': value === 1,
            'text-primary hover:text-primary-dark': value > 1,
          }"
          @click="value > 1 ? value-- : undefined"
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
