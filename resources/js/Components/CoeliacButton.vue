<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { computed, FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue';
import Loader from '@/Components/Loader.vue';

const props = withDefaults(
  defineProps<{
    label?: string;
    theme?: 'primary' | 'faded' | 'secondary' | 'light' | 'negative';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'xxl';
    bold?: boolean;
    as?: typeof Link | 'button' | 'a';
    type?: 'submit' | 'button';
    href?: string;
    icon?:
      | string
      | false
      | FunctionalComponent<HTMLAttributes & VNodeProps>
      | (() => void);
    iconPosition?: 'left' | 'right' | 'center';
    loading?: boolean;
    classes?: string;
    disabled?: boolean;
    iconOnly?: boolean;
    target?: string;
    iconClasses?: string;
  }>(),
  {
    label: undefined,
    theme: 'primary',
    size: 'md',
    bold: false,
    as: Link,
    type: undefined,
    href: undefined,
    icon: false,
    iconPosition: 'left',
    loading: false,
    classes: '',
    disabled: false,
    iconOnly: false,
    target: undefined,
    iconClasses: '',
  },
);

const classes = computed((): string[] => {
  const base = [
    'inline-flex',
    'items-center',
    'rounded-md',
    'border',
    'border-transparent',
    'font-medium',
    'shadow-xs',
    'transition',
    'relative',
    'cursor-pointer',
  ];

  switch (props.size) {
    case 'sm': {
      base.push('px-3', 'py-2', 'text-sm leading-none');
      break;
    }
    case 'md':
    default: {
      base.push('px-4', 'py-2', 'text-sm');
      break;
    }
    case 'lg': {
      base.push('px-4', 'py-2', 'text-base');
      break;
    }
    case 'xl': {
      base.push('px-6', 'py-3', 'text-base', 'md:text-lg');
      break;
    }
    case 'xxl': {
      base.push('px-12', 'py-3', 'text-lg', 'font-semibold', 'md:text-xl');
      break;
    }
  }

  if (props.bold) {
    base.push('font-semibold!');
  }

  if (props.icon && props.iconPosition === 'left') {
    base.push('flex-row-reverse');
  }

  if (props.icon && props.iconPosition === 'center') {
    base.push('justify-center');
  }

  if (props.icon && !props.iconOnly) {
    base.push('space-x-2');
  }

  if (props.theme === 'primary') {
    base.push('bg-primary/80', 'hover:bg-primary', 'text-white');
  }

  if (props.theme === 'light') {
    base.push('bg-primary-light/80', 'hover:bg-primary-light', 'text-black');
  }

  if (props.theme === 'secondary') {
    base.push('bg-secondary/80', 'hover:bg-secondary', 'text-black');
  }

  if (props.theme === 'negative') {
    base.push('bg-red-dark/80', 'hover:bg-red-dark', 'text-white');
  }

  if (props.disabled) {
    base.push('cursor-not-allowed', 'opacity-50');
  }

  base.push(props.classes);

  return base;
});

const emits = defineEmits(['click']);

const isLinkComponent = computed(() => {
  if (props.as === 'a') {
    return true;
  }

  if (!(props.as instanceof Object)) {
    return false;
  }

  return props.as?.name === 'Link';
});
</script>

<template>
  <component
    :is="props.as"
    :class="classes"
    v-bind="{
      ...(props.as === 'button' ? { type: props.type } : null),
      ...(isLinkComponent || props.as === 'a' ? { href: props.href } : null),
      ...(props.as === 'button' && props.disabled ? { disabled: true } : null),
      ...((isLinkComponent || props.as === 'a') && props.target
        ? { target: props.target }
        : null),
    }"
    @click="emits('click')"
  >
    <span
      v-if="!iconOnly"
      :class="{ 'opacity-0': loading }"
      v-text="label"
    />

    <component
      :is="icon"
      v-if="icon"
      :class="[
        {
          '-mr-0.5 ml-2': !iconOnly && iconPosition === 'right',
          '-ml-0.5 mr-2': !iconOnly && iconPosition === 'left',
          'h-5 w-5': iconOnly,
          'h-4 w-4': !iconOnly,
          'opacity-0': loading,
          'h-6 w-6': size === 'xxl',
        },
        iconClasses,
      ]"
      aria-hidden="true"
    />

    <Loader
      :display="loading || false"
      :color="theme === 'faded' ? 'primary' : 'white'"
    />
  </component>
</template>
