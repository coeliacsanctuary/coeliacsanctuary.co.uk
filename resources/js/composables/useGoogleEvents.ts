export default () => {
  const googleEvent = (
    key: 'event',
    event: string,
    attributes: object = {},
  ) => {
    if (typeof window === 'undefined') {
      return;
    }

    console.log('pushing event to google');

    window?.gtag(key, event, attributes);
  };

  return { googleEvent };
};
