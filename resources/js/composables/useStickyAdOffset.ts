import { onMounted, onUnmounted } from 'vue';

const ADHESION_SELECTOR = '.adhesion_wrapper';
const VIDEO_SELECTOR = '#universalPlayer_wrapper';

const adhesionElements = new Set<Element>();
const videoElements = new Set<Element>();
let resizeObserver: ResizeObserver | null = null;
let mutationObserver: MutationObserver | null = null;
let refCount = 0;

const updateCssVars = (): void => {
  let adhesionHeight = 0;
  let videoHeight = 0;

  adhesionElements.forEach((el) => {
    adhesionHeight = Math.max(adhesionHeight, el.getBoundingClientRect().height);
  });

  videoElements.forEach((el) => {
    videoHeight = Math.max(videoHeight, el.getBoundingClientRect().height);
  });

  document.documentElement.style.setProperty('--sticky-bottom', `${adhesionHeight}px`);
  document.documentElement.style.setProperty('--sticky-bottom-right', `${Math.max(adhesionHeight, videoHeight)}px`);
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
  mutationObserver.observe(document.body, { childList: true });
  scan();
};

const teardown = (): void => {
  resizeObserver?.disconnect();
  resizeObserver = null;
  mutationObserver?.disconnect();
  mutationObserver = null;
  adhesionElements.clear();
  videoElements.clear();
  document.documentElement.style.removeProperty('--sticky-bottom');
  document.documentElement.style.removeProperty('--sticky-bottom-right');
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
