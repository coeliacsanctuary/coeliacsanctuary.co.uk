<script setup lang="ts">
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { EateryNationwideBranch } from '@/types/EateryTypes';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

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

  if (branch.area) {
    return `${name}, ${branch.area.name}, ${suffix}`;
  }

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
          class="text-left font-semibold text-primary-dark lg:max-xl:text-lg xl:text-xl"
          v-text="branchName(branch)"
        />

        <span
          v-if="!open"
          class="text-left text-xs lg:text-base"
          v-text="branch.location.address"
        />
      </div>
      <ChevronDownIcon
        :class="open ? 'rotate-180 transform' : ''"
        class="h-5 w-5 text-primary-dark"
      />
    </DisclosureButton>

    <DisclosurePanel class="lex mt-2 flex-col space-y-3">
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
        class="text-lg font-semibold text-primary-dark transition hover:text-black"
      >
        Read more...
      </Link>
    </DisclosurePanel>
  </Disclosure>
</template>
