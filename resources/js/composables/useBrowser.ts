import { usePage } from '@inertiajs/vue3';

export default () => {
  const hasWindowObject: boolean = typeof window !== 'undefined';

  const currentUrl = (): string => {
    if (!hasWindowObject) {
      return usePage().url;
    }

    return window.location.href;
  };

  const currentPath = (): string => {
    if (!hasWindowObject) {
      return usePage().url.split('?')[0];
    }

    return window.location.pathname;
  };

  const absoluteUrl = (): string => {
    if (!hasWindowObject) {
      return `https://www.coeliacsanctaury.co.uk/${usePage().url}`;
    }

    return window.location.origin + window.location.pathname;
  };

  const pageWidth = (
    _default: undefined | number = undefined,
  ): undefined | number => {
    if (!hasWindowObject) {
      return _default;
    }

    return window.screen.width;
  };

  const pageHeight = (
    _default: undefined | number = undefined,
  ): undefined | number => {
    if (!hasWindowObject) {
      return _default;
    }

    return window.screen.height;
  };

  const replaceHistory = (
    url: string,
    data: null | undefined | object = undefined,
  ): undefined => {
    if (!hasWindowObject) {
      return;
    }

    window.history.replaceState(data, '', url);
  };

  return {
    currentUrl,
    currentPath,
    absoluteUrl,
    pageWidth,
    pageHeight,
    replaceHistory,
  };
};
