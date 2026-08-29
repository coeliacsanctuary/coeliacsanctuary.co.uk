import { nextTick, onMounted, onUnmounted, ref, ShallowRef } from 'vue';

/**
 * Works out how many children of a single line flex row fit before it
 * overflows, leaving room for a trailing overflow indicator.
 *
 * The row is measured rather than calculated, so gaps, padding and font loading
 * stay the browsers problem rather than ours. The row needs `overflow-hidden`
 * and children that can't shrink, otherwise nothing ever overflows to measure.
 */
export default (
  container: Readonly<ShallowRef<HTMLElement | null>>,
  totalItems: () => number,
) => {
  const visibleCount = ref(totalItems());

  let observer: ResizeObserver | null = null;
  let isMeasuring = false;

  const childrenOf = (element: HTMLElement): HTMLElement[] =>
    Array.from(element.children) as HTMLElement[];

  const fitsWithin = (child: HTMLElement, edge: number): boolean =>
    child.getBoundingClientRect().right <= edge + 0.5;

  const recalculate = async (): Promise<void> => {
    const element = container.value;

    if (!element || isMeasuring) {
      return;
    }

    isMeasuring = true;

    visibleCount.value = totalItems();

    await nextTick();

    const edge = element.getBoundingClientRect().right;

    const fitted = childrenOf(element).filter((child) =>
      fitsWithin(child, edge),
    ).length;

    if (fitted < totalItems()) {
      visibleCount.value = fitted;

      await nextTick();

      /** The indicator takes up room of its own, so back off until it fits too. */
      while (visibleCount.value > 0) {
        const children = childrenOf(element);
        const indicator = children[children.length - 1];

        if (!indicator || fitsWithin(indicator, edge)) {
          break;
        }

        visibleCount.value -= 1;

        await nextTick();
      }
    }

    isMeasuring = false;
  };

  onMounted(() => {
    if (import.meta.env.SSR || typeof ResizeObserver === 'undefined') {
      return;
    }

    void recalculate();

    void document.fonts?.ready.then(() => recalculate());

    observer = new ResizeObserver(() => void recalculate());

    if (container.value) {
      observer.observe(container.value);
    }
  });

  onUnmounted(() => observer?.disconnect());

  return { visibleCount };
};
