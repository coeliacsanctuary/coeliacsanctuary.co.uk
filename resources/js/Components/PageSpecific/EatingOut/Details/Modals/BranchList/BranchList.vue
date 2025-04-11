<script setup lang="ts">
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { EateryNationwideBranch } from '@/types/EateryTypes';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';

const props = defineProps<{
  branch: EateryNationwideBranch;
  eateryName: string;
}>();

const branchName = (branch: EateryNationwideBranch): string => {
  const suffix =
    branch.town.name === branch.county.name
      ? branch.town.name
      : `${branch.town.name}, ${branch.county.name}`;

  const name = branch.name ? branch.name : props.eateryName;

  return `${name}, ${suffix}`;
};
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
      <div class="flex flex-col space-y-1">
        <span
          class="text-left text-primary-dark font-semibold lg:max-xl:text-lg xl:text-xl"
          v-text="branchName(branch)"
        />

        <span
          v-if="!open"
          class="text-xs text-left lg:text-base"
          v-text="branch.location.address"
        />
      </div>
      <ChevronDownIcon
        :class="open ? 'rotate-180 transform' : ''"
        class="h-5 w-5 text-primary-dark"
      />
    </DisclosureButton>

    <DisclosurePanel class="lex flex-col space-y-3 mt-2">
      <StaticMap
        :lng="branch.location.lng"
        :lat="branch.location.lat"
        :title="`${branch.name} - ${branch.location.address}`"
      />

      <div
        class="font-semibold lg:text-lg"
        v-text="branch.location.address"
      />

      <Link
        :href="branch.link"
        class="font-semibold text-primary-dark hover:text-black transition text-lg"
      >
        Read more...
      </Link>
    </DisclosurePanel>
  </Disclosure>
</template>
