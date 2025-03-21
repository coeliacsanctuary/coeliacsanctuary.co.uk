<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

defineProps<{ county: string }>();
</script>

<template>
  <Disclosure
    v-slot="{ open }"
    :as="Card"
    v-bind="{
      noPadding: true,
      class: 'py-1',
      shadow: false,
    }"
  >
    <DisclosureButton
      class="flex w-full justify-between px-2 focus:outline-hidden"
      :class="{ 'pb-2 border-b border-primary-light/50': !open }"
    >
      <span
        class="text-left text-primary-dark font-semibold lg:max-xl:text-lg xl:text-xl"
        v-text="county"
      />
      <ChevronDownIcon
        :class="open ? 'rotate-180 transform' : ''"
        class="h-5 w-5 text-primary-dark"
      />
    </DisclosureButton>

    <DisclosurePanel class="flex flex-col space-y-3 mt-4 mx-2">
      <slot />
    </DisclosurePanel>
  </Disclosure>
</template>
