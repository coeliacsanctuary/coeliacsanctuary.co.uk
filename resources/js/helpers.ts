import dayjs from 'dayjs';
import advancedFormat from 'dayjs/plugin/advancedFormat';

export const formatDate = (
  date: string,
  format: string = 'Do MMM YYYY',
): string => {
  dayjs.extend(advancedFormat);

  return dayjs(date).format(format);
};

export const loadScript = (script: string): Promise<unknown> => {
  if (typeof document === 'undefined') {
    return new Promise(() => {});
  }

  return new Promise((resolve) => {
    if (document.querySelector(`script[src="${script}"]`)) {
      resolve(true);

      return;
    }

    const scriptElement = document.createElement('script');

    scriptElement.setAttribute('src', script);

    document.body.appendChild(scriptElement);

    scriptElement.addEventListener('load', resolve);
  });
};

export const ucfirst = (str: string): string =>
  str.charAt(0).toUpperCase() + str.slice(1);

export const pluralise = (str: string, count: number): string => {
  if (count === 1) {
    return str;
  }

  if (str.endsWith('y')) {
    return str.replace(/y$/, 'ies');
  }

  if (str.endsWith('ch')) {
    return `${str}es`;
  }

  return `${str}s`;
};

export type LatLng = { lat: number; lng: number };

const EARTH_RADIUS_MILES = 3958.8;

const toRadians = (degrees: number): number => (degrees * Math.PI) / 180;

/** Great circle distance between two points, in miles. */
export const distanceInMiles = (from: LatLng, to: LatLng): number => {
  const deltaLat = toRadians(to.lat - from.lat);
  const deltaLng = toRadians(to.lng - from.lng);

  const a =
    Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
    Math.cos(toRadians(from.lat)) *
      Math.cos(toRadians(to.lat)) *
      Math.sin(deltaLng / 2) *
      Math.sin(deltaLng / 2);

  return EARTH_RADIUS_MILES * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

export const getTitle = (title: string | undefined): string => {
  const appName = 'Coeliac Sanctuary';

  if (!title || title === '' || title === appName) {
    title = 'Gluten Free Recipes, Blog & UK Places to Eat';
  }

  return `${title} - ${appName}`;
};
