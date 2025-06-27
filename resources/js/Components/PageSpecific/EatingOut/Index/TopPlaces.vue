<script setup lang="ts">
import useScreensize from '@/composables/useScreensize';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import SubHeading from '@/Components/SubHeading.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';
import Card from '@/Components/Card.vue';

withDefaults(defineProps<{ showBreakpoint?: string }>(), {
  showBreakpoint: 'md',
});
</script>

<template>
  <Disclosure
    v-slot="{ open }"
    :default-open="useScreensize().screenIsGreaterThanOrEqualTo(showBreakpoint)"
  >
    <Card class="mt-3 flex flex-col space-y-4">
      <DisclosureButton>
        <div class="flex items-center justify-between">
          <SubHeading>
            <slot name="title" />
          </SubHeading>
          <ChevronDownIcon
            :class="open ? 'rotate-180 transform' : ''"
            class="size-8 flex-shrink-0 transition"
          />
        </div>
      </DisclosureButton>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-out"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <DisclosurePanel>
          <slot />
        </DisclosurePanel>
      </transition>
    </Card>
  </Disclosure>
</template>
