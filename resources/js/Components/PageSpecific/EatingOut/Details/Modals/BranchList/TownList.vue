<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

defineProps<{ town: string }>();
</script>

<template>
  <Disclosure
    v-slot="{ open }"
    :as="Card"
    v-bind="{
      theme: 'primary-light',
      faded: true,
      noPadding: true,
      class: 'p-2',
    }"
  >
    <DisclosureButton
      class="flex w-full justify-between rounded-lg focus:outline-hidden"
    >
      <span
        class="text-left font-semibold text-primary-dark lg:max-xl:text-lg xl:text-xl"
        v-text="town"
      />
      <ChevronDownIcon
        :class="open ? 'rotate-180 transform' : ''"
        class="h-5 w-5 text-primary-dark"
      />
    </DisclosureButton>

    <DisclosurePanel class="mt-4 flex flex-col space-y-3 rounded bg-white p-2">
      <slot />
    </DisclosurePanel>
  </Disclosure>
</template>
