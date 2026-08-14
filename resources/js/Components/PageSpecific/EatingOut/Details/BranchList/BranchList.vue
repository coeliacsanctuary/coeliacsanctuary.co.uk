<script setup lang="ts">
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { EateryNationwideBranch } from '@/types/EateryTypes';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    branch: EateryNationwideBranch;
    eateryName: string;
    forceOpen?: boolean;
    /** Miles from the visitor, shown only when they've used "near me". */
    distance?: number;
  }>(),
  {
    forceOpen: false,
    distance: undefined,
  },
);

const isOpen = ref(false);

const open = computed(() => props.forceOpen || isOpen.value);

const branchName = computed(() => {
  const suffix =
    props.branch.town.name === props.branch.county.name
      ? props.branch.town.name
      : `${props.branch.town.name}, ${props.branch.county.name}`;

  const name = props.branch.name ? props.branch.name : props.eateryName;

  if (props.branch.area) {
    return `${name}, ${props.branch.area.name}, ${suffix}`;
  }

  return `${name}, ${suffix}`;
});
</script>

<template>
  <Card
    :id="`branch-${branch.id}`"
    theme="primary-light"
    faded
    no-padding
    class="p-2"
  >
    <button
      type="button"
      class="flex w-full cursor-pointer items-start justify-between gap-2 text-left focus:outline-hidden"
      @click="isOpen = !isOpen"
    >
      <span class="flex flex-1 flex-col space-y-1">
        <span
          class="text-sm font-semibold text-primary-dark lg:text-base"
          v-text="branchName"
        />

        <span
          v-if="distance !== undefined"
          class="text-xs font-semibold text-grey-darker"
          v-text="`${distance.toFixed(1)} miles away`"
        />

        <span
          v-if="!open"
          class="text-xs text-grey-dark"
          v-text="branch.location.address"
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
        class="mt-2 flex flex-col space-y-3"
      >
        <StaticMap
          map-classes="min-h-map-small"
          :lng="branch.location.lng"
          :lat="branch.location.lat"
          :title="`${branchName} - ${branch.location.address}`"
        />

        <div
          class="text-sm font-semibold"
          v-text="branch.location.address"
        />

        <Link
          :href="branch.link"
          class="text-sm font-semibold text-primary-dark transition hover:text-black"
        >
          Read more...
        </Link>
      </div>
    </transition>
  </Card>
</template>
