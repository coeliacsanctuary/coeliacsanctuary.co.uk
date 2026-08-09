<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { computed, ref } from 'vue';

/**
 * A controlled accordion for the branch list levels.
 *
 * HeadlessUI's Disclosure only takes `defaultOpen` and exposes no programmatic
 * open, so it can't be driven from outside once mounted. Searching and "near me"
 * both need exactly that, hence the hand rolled open state here.
 */
const props = withDefaults(
  defineProps<{
    label: string;
    count?: number;
    forceOpen?: boolean;
    plain?: boolean;
  }>(),
  {
    count: undefined,
    forceOpen: false,
    plain: false,
  },
);

const isOpen = ref(false);

const open = computed(() => props.forceOpen || isOpen.value);
</script>

<template>
  <Card
    :theme="plain ? 'white' : 'primary-light'"
    :faded="!plain"
    :shadow="!plain"
    no-padding
    :class="plain ? 'py-1' : 'p-2'"
  >
    <button
      type="button"
      class="flex w-full cursor-pointer items-center justify-between gap-2 text-left focus:outline-hidden"
      :class="{
        'px-2': plain,
        'border-b border-primary-light/50 pb-2': plain && !open,
      }"
      @click="isOpen = !isOpen"
    >
      <span class="flex flex-1 items-baseline gap-2">
        <span
          class="text-sm font-semibold text-primary-dark lg:text-base"
          v-text="label"
        />

        <span
          v-if="count !== undefined"
          class="text-xs text-grey-dark"
          v-text="count"
        />
      </span>

      <ChevronDownIcon
        class="size-5 shrink-0 text-primary-dark transition"
        :class="{ 'rotate-180': open }"
      />
    </button>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-out"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-show="open"
        class="mt-3 flex flex-col space-y-2"
        :class="plain ? 'mx-2' : 'rounded-sm bg-white p-2'"
      >
        <slot />
      </div>
    </transition>
  </Card>
</template>
