<script lang="ts" setup>
import { ref, watch } from 'vue';
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { ArticleFaq } from '@/types/Types';

const props = defineProps<{
  faq: ArticleFaq;
  index: number;
  isOpen: boolean;
}>();

defineEmits<{
  open: [index: number];
}>();

const panel = ref<HTMLElement | null>(null);

watch(
  () => props.isOpen,
  (open) => {
    const el = panel.value;

    if (!el) {
      return;
    }

    if (open) {
      el.style.overflow = 'hidden';
      el.style.transition = 'height 0.3s ease-out';
      el.style.height = `${el.scrollHeight}px`;
      el.addEventListener(
        'transitionend',
        () => {
          el.style.height = 'auto';
          el.style.overflow = '';
          el.style.transition = '';
        },
        { once: true },
      );

      return;
    }

    el.style.height = `${el.scrollHeight}px`;
    el.style.overflow = 'hidden';
    requestAnimationFrame(() => {
      el.style.transition = 'height 0.3s ease-in';
      el.style.height = '0';
    });
  },
);
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

    <div
      ref="panel"
      style="height: 0; overflow: hidden"
    >
      <div class="border-t border-primary-light px-3 pb-3 pt-2">
        <p
          class="prose prose-lg max-w-none md:prose-xl"
          v-html="faq.answer"
        />
      </div>
    </div>
  </div>
</template>
