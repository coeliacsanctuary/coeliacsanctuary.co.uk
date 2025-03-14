import dayjs from 'dayjs';
import advancedFormat from 'dayjs/plugin/advancedFormat';
import { Converter } from 'any-number-to-words';

export const formatDate = (
  date: string,
  format: string = 'Do MMM YYYY',
): string => {
  dayjs.extend(advancedFormat);

  return dayjs(date).format(format);
};

export const numberToWords = (
  number: number,
  min: number = 0,
  max: number = 10,
): string => {
  if (number <= min || number >= max) {
    return number.toLocaleString();
  }

  return new Converter().toWords(number);
};

export const loadScript = (script: string) => {
  if (typeof document === 'undefined') {
    return;
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

export const getTitle = (title: string | undefined): string => {
  const appName = 'Coeliac Sanctuary';

  return title && title !== '' && title !== appName
    ? `${title} - ${appName}`
    : 'Coeliac Sanctuary - Coeliac Blog, Gluten Free Places to Eat, Reviews, and more!';
};
