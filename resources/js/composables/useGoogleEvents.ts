export default () => {
  const googleEvent = (
    key: 'event',
    event: string,
    attributes: object = {},
  ) => {
    if (
      typeof window === 'undefined' ||
      import.meta.env.VITE_APP_ENV === 'local'
    ) {
      return;
    }

    window.gtag(key, event, attributes);
  };

  return { googleEvent };
};
