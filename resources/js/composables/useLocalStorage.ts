export default () => {
  const putInLocalStorage = (name: string, value: unknown): void => {
    localStorage.setItem(name, JSON.stringify(value));
  };

  const getFromLocalStorage = <T>(
    name: string,
    defaultValue: T | null = null,
  ): T | null => {
    const rtr = localStorage.getItem(name);

    if (!rtr) {
      return defaultValue;
    }

    return JSON.parse(rtr) as T;
  };

  const removeFromLocalStorage = (key: string): void => {
    localStorage.removeItem(key);
  };

  return { putInLocalStorage, getFromLocalStorage, removeFromLocalStorage };
};
