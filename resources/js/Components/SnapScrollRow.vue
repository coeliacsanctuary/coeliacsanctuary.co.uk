<script setup lang="ts" generic="T">
import {
  computed,
  onBeforeUnmount,
  onMounted,
  ref,
  useTemplateRef,
  watch,
} from 'vue';

const props = withDefaults(
  defineProps<{
    items: T[];
    carousel?: boolean;
    gridClasses?: string;
    itemClasses?: string;
    fadeClasses?: string;
    label?: (item: T, index: number) => string;
  }>(),
  {
    carousel: false,
    gridClasses: 'sm:grid-cols-2 xl:grid-cols-3',
    itemClasses: 'w-[78%] shrink-0 snap-start sm:w-auto sm:shrink',
    fadeClasses: 'from-white',
    label: undefined,
  },
);

const tileRow = useTemplateRef<HTMLElement>('tileRow');
const perPage = ref(1);
const activePage = ref(0);

const pageCount = computed(() =>
  Math.max(1, Math.ceil(props.items.length / perPage.value)),
);

/** Work out how many tiles fit on screen at once from the rendered geometry. */
const measure = (): void => {
  const row = tileRow.value;

  if (!row || row.children.length === 0) {
    return;
  }

  const first = row.children[0] as HTMLElement;
  const second = row.children[1] as HTMLElement | undefined;
  const stride = second
    ? second.offsetLeft - first.offsetLeft
    : first.offsetWidth;

  if (stride > 0) {
    perPage.value = Math.max(1, Math.round(row.clientWidth / stride));
  }
};

/** Track how far through the scrollable range the row is, to mark the active dot. */
const updateActivePage = (): void => {
  const row = tileRow.value;

  if (!row) {
    return;
  }

  const furthest = row.scrollWidth - row.clientWidth;

  activePage.value =
    furthest <= 0
      ? 0
      : Math.round((row.scrollLeft / furthest) * (pageCount.value - 1));
};

const scrollToPage = (page: number): void => {
  const row = tileRow.value;

  if (!row) {
    return;
  }

  const furthest = row.scrollWidth - row.clientWidth;

  row.scrollTo({
    left: pageCount.value > 1 ? (page / (pageCount.value - 1)) * furthest : 0,
    behavior: 'smooth',
  });
};

const refresh = (): void => {
  measure();
  updateActivePage();
};

onMounted(() => {
  refresh();

  window.addEventListener('resize', refresh);
});

onBeforeUnmount(() => window.removeEventListener('resize', refresh));

watch(() => props.items, refresh);

const dotLabel = (page: number): string => {
  const item = props.items[page * perPage.value];

  if (!item || !props.label) {
    return `Show item ${page * perPage.value + 1}`;
  }

  return props.label(item, page * perPage.value);
};
</script>

<template>
  <div class="flex flex-col space-y-3">
    <div class="relative">
      <div
        ref="tileRow"
        class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2"
        :class="
          carousel ? '' : ['sm:grid sm:overflow-visible sm:pb-0', gridClasses]
        "
        @scroll="updateActivePage"
      >
        <slot
          v-for="(item, index) in items"
          :key="index"
          :item="item"
          :index="index"
          :item-classes="itemClasses"
        />
      </div>

      <div
        v-if="pageCount > 1"
        class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-linear-to-l to-transparent"
        :class="[fadeClasses, carousel ? '' : 'sm:hidden']"
      />
    </div>

    <div
      v-if="pageCount > 1"
      class="flex justify-center space-x-2"
      :class="carousel ? '' : 'sm:hidden'"
    >
      <button
        v-for="page in pageCount"
        :key="page"
        type="button"
        class="size-2.5 cursor-pointer rounded-full transition"
        :class="page - 1 === activePage ? 'bg-primary-dark' : 'bg-grey-off'"
        :aria-label="dotLabel(page - 1)"
        @click="scrollToPage(page - 1)"
      />
    </div>
  </div>
</template>
