<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import SubHeading from '@/Components/SubHeading.vue';
import { ProductAdditionalDetailAccordionProps } from '@/types/Shop';

withDefaults(defineProps<ProductAdditionalDetailAccordionProps>(), {
  openByDefault: false,
  content: undefined,
  component: undefined,
  props: undefined,
  headerComponent: 'button',
  headerClasses: '',
  wrapperComponent: 'div',
  wrapperClasses: '',
  panelClasses: '',
});
</script>

<template>
  <Disclosure
    v-slot="{ open }"
    :as="wrapperComponent as string"
    :default-open="openByDefault"
    :class="wrapperClasses"
  >
    <DisclosureButton
      class="group relative flex w-full cursor-pointer items-center justify-between py-2 text-left hover:text-primary-dark"
      :as="headerComponent as string"
      :class="headerClasses"
    >
      <SubHeading
        as="h3"
        :classes="open ? 'text-primary-dark' : ''"
      >
        {{ title }}
      </SubHeading>
      <span class="ml-6 flex items-center">
        <PlusIcon
          v-if="!open"
          class="block h-6 w-6"
          aria-hidden="true"
        />
        <MinusIcon
          v-else
          class="block h-6 w-6"
          aria-hidden="true"
        />
      </span>
    </DisclosureButton>

    <DisclosurePanel
      :id="title"
      as="div"
      :class="panelClasses"
    >
      <div
        v-if="content"
        class="prose prose-lg max-w-none lg:prose-xl"
        v-html="content"
      />
      <Component
        :is="component"
        v-else-if="component"
        v-bind="props"
      />
    </DisclosurePanel>
  </Disclosure>
</template>
