<script lang="ts" setup>
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { ArticleFaq } from '@/types/Types';

defineProps<{
  faq: ArticleFaq;
  index: number;
  isOpen: boolean;
}>();

defineEmits<{
  open: [index: number];
}>();

const onEnter = (el: Element): void => {
  const element = el as HTMLElement;
  element.style.height = '0';
  element.style.overflow = 'hidden';
  requestAnimationFrame(() => {
    element.style.transition = 'height 0.3s ease-out';
    element.style.height = `${element.scrollHeight}px`;
  });
};

const onAfterEnter = (el: Element): void => {
  const element = el as HTMLElement;
  element.style.height = 'auto';
  element.style.overflow = '';
  element.style.transition = '';
};

const onLeave = (el: Element): void => {
  const element = el as HTMLElement;
  element.style.height = `${element.scrollHeight}px`;
  element.style.overflow = 'hidden';
  requestAnimationFrame(() => {
    element.style.transition = 'height 0.3s ease-in';
    element.style.height = '0';
  });
};

const onAfterLeave = (el: Element): void => {
  const element = el as HTMLElement;
  element.style.height = '';
  element.style.overflow = '';
  element.style.transition = '';
};
</script>

<template>
  <div class="rounded-sm border border-primary-light bg-white shadow-sm">
    <button
      type="button"
      class="flex w-full cursor-pointer items-center justify-between px-3 py-2 text-left transition duration-300 hover:bg-primary-lightest"
      :class="isOpen ? 'rounded-t-sm bg-primary-lightest' : 'rounded-sm bg-primary-lightest/50'"
      :aria-expanded="isOpen"
      @click="$emit('open', index)"
    >
      <h3 class="text-base font-semibold md:text-lg">
        {{ faq.question }}
      </h3>

      <span class="ml-6 flex shrink-0 items-center">
        <PlusIcon
          v-if="!isOpen"
          class="block h-6 w-6"
          aria-hidden="true"
        />

        <MinusIcon
          v-else
          class="block h-6 w-6"
          aria-hidden="true"
        />
      </span>
    </button>

    <Transition
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @leave="onLeave"
      @after-leave="onAfterLeave"
    >
      <div v-if="isOpen">
        <div class="border-t border-primary-light px-3 pb-3 pt-2">
          <p
            class="prose prose-lg max-w-none md:prose-xl"
            v-html="faq.answer"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>
