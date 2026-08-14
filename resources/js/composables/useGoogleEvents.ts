export default () => {
  const googleEvent = (
    key: 'event',
    event: string,
    attributes: object = {},
  ) => {
    if (typeof window === 'undefined' || window?.gtag === undefined) {
      return;
    }

    window.gtag(key, event, attributes);
  };

  return { googleEvent };
};
