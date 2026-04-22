import { onMounted, onUnmounted, ref } from 'vue';
import type { Ref } from 'vue';

const ADHESION_SELECTOR = '.adhesion_wrapper';
const VIDEO_SELECTOR = '#universalPlayer_wrapper';

const adhesionElements = new Set<Element>();
const videoElements = new Set<Element>();
let resizeObserver: ResizeObserver | null = null;
let mutationObserver: MutationObserver | null = null;
let refCount = 0;

const adhesionHeight: Ref<number> = ref(0);

const viewportCoverage = (el: Element): number => {
  const rect = el.getBoundingClientRect();

  if (rect.height === 0) {
    return 0;
  }

  return Math.max(0, window.innerHeight - rect.top);
};

const updateCssVars = (): void => {
  let adhesionCoverage = 0;
  let videoCoverage = 0;

  adhesionElements.forEach((el) => {
    adhesionCoverage = Math.max(adhesionCoverage, viewportCoverage(el));
  });

  videoElements.forEach((el) => {
    videoCoverage = Math.max(videoCoverage, viewportCoverage(el));
  });

  adhesionHeight.value = adhesionCoverage;

  document.documentElement.style.setProperty('--sticky-bottom', `${adhesionCoverage}px`);
  document.documentElement.style.setProperty('--sticky-bottom-right', `${Math.max(adhesionCoverage, videoCoverage)}px`);
};

const observeElement = (el: Element, set: Set<Element>): void => {
  if (set.has(el)) {
    return;
  }

  set.add(el);
  resizeObserver?.observe(el);
};

const scan = (): void => {
  document.querySelectorAll(ADHESION_SELECTOR).forEach((el) => observeElement(el, adhesionElements));
  document.querySelectorAll(VIDEO_SELECTOR).forEach((el) => observeElement(el, videoElements));
};

const setup = (): void => {
  resizeObserver = new ResizeObserver(updateCssVars);
  mutationObserver = new MutationObserver(scan);
  mutationObserver.observe(document.body, { childList: true, subtree: true });
  scan();
};

const teardown = (): void => {
  resizeObserver?.disconnect();
  resizeObserver = null;
  mutationObserver?.disconnect();
  mutationObserver = null;
  adhesionElements.clear();
  videoElements.clear();
  adhesionHeight.value = 0;
  document.documentElement.style.removeProperty('--sticky-bottom');
  document.documentElement.style.removeProperty('--sticky-bottom-right');
};

export default function useStickyAdOffset(): { adhesionHeight: Ref<number> } {
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

  return { adhesionHeight };
}
