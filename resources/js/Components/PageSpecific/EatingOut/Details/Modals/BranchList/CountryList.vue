<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

defineProps<{ country: string }>();
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
        class="text-left text-primary-dark font-semibold lg:max-xl:text-lg xl:text-xl"
        v-text="country"
      />
      <ChevronDownIcon
        :class="open ? 'rotate-180 transform' : ''"
        class="h-5 w-5 text-primary-dark"
      />
    </DisclosureButton>

    <DisclosurePanel class="flex flex-col space-y-1 mt-4 p-2 rounded bg-white">
      <slot />
    </DisclosurePanel>
  </Disclosure>
</template>
