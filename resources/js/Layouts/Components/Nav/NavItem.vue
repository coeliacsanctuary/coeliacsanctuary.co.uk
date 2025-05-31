<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

withDefaults(
  defineProps<{
    label: string;
    href?: string;
    active?: boolean;
  }>(),
  {
    href: undefined,
    active: false,
  },
);
</script>

<template>
  <Link
    :class="{
      'border-b-4 border-secondary': active,
      'border-b-2 border-transparent hover:border-white': !active,
    }"
    :href="href"
    class="flex h-full items-center px-4 font-coeliac text-2xl leading-none font-medium tracking-wider lg:text-3xl"
    prefetch
  >
    {{ label }}
  </Link>
</template>
