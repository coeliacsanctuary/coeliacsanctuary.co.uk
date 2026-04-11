const isBrowser = typeof localStorage !== 'undefined';

export default () => {
  const putInLocalStorage = (name: string, value: unknown): void => {
    if (!isBrowser) {
      return;
    }

    localStorage.setItem(name, JSON.stringify(value));
  };

  const isInLocalStorage = (name: string): boolean => {
    if (!isBrowser) {
      return false;
    }

    return localStorage.getItem(name) !== null;
  };

  const getFromLocalStorage = <T>(
    name: string,
    defaultValue: T | null = null,
  ): T | null => {
    if (!isBrowser) {
      return defaultValue;
    }

    const rtr = localStorage.getItem(name);

    if (!rtr) {
      return defaultValue;
    }

    return JSON.parse(rtr) as T;
  };

  const removeFromLocalStorage = (key: string): void => {
    if (!isBrowser) {
      return;
    }

    localStorage.removeItem(key);
  };

  return {
    putInLocalStorage,
    isInLocalStorage,
    getFromLocalStorage,
    removeFromLocalStorage,
  };
};
