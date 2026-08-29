<script setup lang="ts">
import { ArrowDownIcon } from '@heroicons/vue/24/solid';
import { computed, onMounted, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    anchor: HTMLElement;
    label: string;
    side?: 'right' | 'left';
    showAfter?: HTMLElement;
  }>(),
  { side: 'right', showAfter: undefined },
);

const anchorIsBelowViewport = ref(true);

const hasScrolledPastShowAfter = ref(!props.showAfter);

const show = computed(
  () => anchorIsBelowViewport.value && hasScrolledPastShowAfter.value,
);

onMounted(() => {
  new IntersectionObserver((entries) => {
    anchorIsBelowViewport.value =
      entries[0].intersectionRatio === 0 &&
      entries[0].boundingClientRect.top > 0;
  }).observe(props.anchor);

  if (!props.showAfter) {
    return;
  }

  new IntersectionObserver((entries) => {
    hasScrolledPastShowAfter.value =
      entries[0].intersectionRatio === 0 &&
      entries[0].boundingClientRect.top < 0;
  }).observe(props.showAfter);
});

const scrollToAnchor = () => {
  props.anchor.scrollIntoView({
    behavior: 'smooth',
  });
};
</script>

<template>
  <teleport to="body">
    <transition
      enter-active-class="duration-500 ease-out"
      enter-class="scale-50 translate-x-full opacity-0"
      enter-to-class="translate-x-0 scale-100 opacity-100"
      leave-active-class="duration-100 ease-in"
      leave-class="translate-x-0 scale-100 opacity-100"
      leave-to-class="translate-x-full scale-50 opacity-0"
    >
      <div
        v-if="show"
        class="safe-bottom fixed left-[50%] mx-auto flex w-full max-w-8xl translate-x-[-50%] pb-4"
        :style="{
          bottom: `var(${side === 'right' ? '--sticky-bottom-right' : '--sticky-bottom'}, 0px)`,
        }"
        :class="{
          'justify-end pr-4': side === 'right',
          'pl-4': side === 'left',
        }"
      >
        <div
          class="w-fit"
          @click="scrollToAnchor()"
        >
          <div
            class="relative cursor-pointer font-semibold text-white transition-all hover:scale-110"
          >
            <div
              class="rounded-full bg-primary py-2 text-lg shadow"
              :class="{
                'pr-16 pl-4': side === 'right',
                'pr-4 pl-16': side === 'left',
              }"
              v-text="label"
            />
            <div
              class="absolute top-[50%] flex size-14 translate-y-[-50%] items-center justify-center rounded-full bg-primary"
              :class="{
                'right-0': side === 'right',
                'left-0': side === 'left',
              }"
            >
              <ArrowDownIcon class="size-12" />
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>
