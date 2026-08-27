import { onUnmounted, watch } from 'vue';

export default function useBodyClass(
  bodyClass: () => string | undefined,
): void {
  let applied = bodyClass();

  const apply = (value: string | undefined): void => {
    if (applied) {
      document.body.classList.remove(applied);
    }

    applied = value;

    if (applied) {
      document.body.classList.add(applied);
    }
  };

  watch(bodyClass, apply);

  onUnmounted(() => apply(undefined));
}
