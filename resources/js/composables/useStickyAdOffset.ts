import { onMounted, onUnmounted } from 'vue';

const SELECTORS = '.adhesion_wrapper';
const CSS_VAR = '--sticky-bottom';

const observedElements = new Set<Element>();
let resizeObserver: ResizeObserver | null = null;
let mutationObserver: MutationObserver | null = null;
let refCount = 0;

const updateCssVar = (): void => {
  let maxHeight = 0;

  observedElements.forEach((el) => {
    maxHeight = Math.max(maxHeight, el.getBoundingClientRect().height);
  });

  document.documentElement.style.setProperty(CSS_VAR, `${maxHeight}px`);
};

const observeElement = (el: Element): void => {
  if (observedElements.has(el)) {
    return;
  }

  observedElements.add(el);
  resizeObserver?.observe(el);
};

const scan = (): void => {
  document.querySelectorAll(SELECTORS).forEach(observeElement);
};

const setup = (): void => {
  resizeObserver = new ResizeObserver(updateCssVar);
  mutationObserver = new MutationObserver(scan);
  mutationObserver.observe(document.body, { childList: true });
  scan();
};

const teardown = (): void => {
  resizeObserver?.disconnect();
  resizeObserver = null;
  mutationObserver?.disconnect();
  mutationObserver = null;
  observedElements.clear();
  document.documentElement.style.removeProperty(CSS_VAR);
};

export default function useStickyAdOffset(): void {
  onMounted(() => {
    if (refCount++ === 0) {
      setup();
    }
  });

  onUnmounted(() => {
    if (--refCount === 0) {
      teardown();
    }
  });
}