<script lang="ts" setup>
import { ArrowLongLeftIcon, ArrowLongRightIcon } from '@heroicons/vue/20/solid';
import useScreensize from '@/composables/useScreensize';

type Page = {
  type: 'page' | 'dots';
  number?: number;
  current?: boolean;
  static?: boolean;
};

const props = defineProps<{
  current: number;
  to: number;
}>();

const canGoBack = (): boolean => props.current > 1;

const canGoForward = (): boolean => props.current < props.to;

const screensize = useScreensize();

const pages = (): Page[] => {
  const pageArray: Page[] = [];

  if (props.current > 3) {
    pageArray.push({ type: 'page', number: 1, static: true });
  }

  if (props.current > 4) {
    pageArray.push({
      type: 'page',
      number: 2,
      static: screensize.screenIsGreaterThanOrEqualTo('sm'),
    });
  }

  if (props.current > 5) {
    pageArray.push({ type: 'dots', number: 2.1 });
  }

  if (props.current >= 3) {
    pageArray.push({
      type: 'page',
      number: props.current - 2,
      static: screensize.screenIsGreaterThanOrEqualTo('sm'),
    });
  }

  if (props.current >= 2) {
    pageArray.push({ type: 'page', number: props.current - 1, static: true });
  }

  pageArray.push({
    type: 'page',
    number: props.current,
    current: true,
    static: true,
  });

  if (props.current < props.to - 2) {
    pageArray.push({ type: 'page', number: props.current + 1, static: true });
  }

  if (props.current < props.to - 1) {
    pageArray.push({
      type: 'page',
      number: props.current + 2,
      static: screensize.screenIsGreaterThanOrEqualTo('sm'),
    });
  }

  if (props.current < props.to - 4) {
    pageArray.push({ type: 'dots', number: props.to - 2 - 0.1, static: true });
  }

  if (props.current + 1 < props.to) {
    pageArray.push({
      type: 'page',
      number: props.to - 1,
      static: screensize.screenIsGreaterThanOrEqualTo('sm'),
    });
  }

  if (props.current < props.to) {
    pageArray.push({ type: 'page', number: props.to, static: true });
  }

  return pageArray.filter(
    (page, index) =>
      pageArray.map((page) => page.number).indexOf(page.number) === index,
  );
};

const emits = defineEmits(['change']);

const gotoPage = (page: number | 'next' | 'prev'): void => {
  if (page === 'next') {
    emits('change', props.current + 1);

    return;
  }

  if (page === 'prev') {
    emits('change', props.current - 1);

    return;
  }

  emits('change', page);
};

const classes = (page: Page): string[] => {
  const rtr: string[] = ['items-center', 'border-y-2', 'p-3', 'font-semibold'];

  if (page.current) {
    rtr.push('border-primary', 'text-primary');
  }

  if (!page.current) {
    rtr.push(
      'border-transparent',
      'text-gray-500',
      'hover:border-gray-300',
      'hover:text-gray-700',
      'cursor-pointer',
    );
  }

  rtr.push(page.static ? 'inline-flex' : 'hidden sm:inline-flex');

  return rtr;
};
</script>

<template>
  <nav
    class="flex items-center justify-between border-t border-gray-200 px-4 pt-4 select-none sm:px-0"
  >
    <div class="-mt-px flex items-center">
      <a
        v-if="canGoBack()"
        class="inline-flex cursor-pointer items-center border-t-2 border-transparent pr-1 font-semibold text-gray-500 hover:border-gray-300 hover:text-gray-700"
        @click.prevent="gotoPage('prev')"
      >
        <ArrowLongLeftIcon class="mr-3 h-5 w-5 text-gray-400" />
        <span class="hidden sm:inline">Previous</span>
      </a>
    </div>

    <div class="flex flex-1 flex-wrap items-center justify-center sm:-mt-px">
      <template
        v-for="page in pages()"
        :key="page.number"
      >
        <a
          v-if="page.type === 'page'"
          :class="classes(page)"
          @click.prevent="gotoPage(page.number)"
          v-text="page.number"
        />
        <span
          v-else
          class="inline-flex items-center border-t-2 border-transparent px-4 text-sm font-medium text-gray-500 sm:inline-flex"
          v-text="'...'"
        />
      </template>
    </div>

    <div class="-mt-px flex justify-end">
      <a
        v-if="canGoForward()"
        class="inline-flex cursor-pointer items-center border-t-2 border-transparent pl-1 font-semibold text-gray-500 hover:border-gray-300 hover:text-gray-700"
        @click.prevent="gotoPage('next')"
      >
        <span class="hidden sm:inline">Next</span>
        <ArrowLongRightIcon class="ml-3 h-5 w-5 text-gray-400" />
      </a>
    </div>
  </nav>
</template>
