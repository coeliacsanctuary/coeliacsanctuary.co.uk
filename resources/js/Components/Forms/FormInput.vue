<script lang="ts" setup>
import { InputPropDefaults, InputProps } from '@/Components/Forms/Props';
import { defineModel } from 'vue';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';

const props = withDefaults(defineProps<InputProps>(), InputPropDefaults);

const emits = defineEmits(['focus', 'blur-sm']);

const [value, modifiers] = defineModel<string | number, string | number>({
  set(v: string | number): string | number {
    if (modifiers.number && typeof v === 'string') {
      return parseInt(v, 10);
    }

    return v;
  },
});

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
      :class="[{ 'shadow-xs': borders }, wrapperClasses]"
    >
      <input
        v-model="value"
        :class="classes()"
        :name="name"
        :required="required"
        :type="type"
        v-bind="{
          ...(id ? { id } : null),
          ...(autocomplete ? { autocomplete } : null),
          ...(placeholder ? { placeholder } : null),
          ...(disabled ? { disabled } : null),
          ...(min ? { min } : null),
          ...(max ? { max } : null),
        }"
        @focus="emits('focus')"
        @blur="emits('blur-sm')"
      />

      <div
        v-if="error"
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
      >
        <ExclamationCircleIcon
          aria-hidden="true"
          class="h-5 w-5"
          :class="{
            'text-red': hideErrorBackground,
            'text-white': !hideErrorBackground,
          }"
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
