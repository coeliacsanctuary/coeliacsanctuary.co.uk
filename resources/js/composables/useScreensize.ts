import { ref } from 'vue';
import useBrowser from '@/composables/useBrowser';

type BreakPoint =
  | 'xxxs'
  | 'xxs'
  | 'xs'
  | 'sm'
  | 'xmd'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl';

type ScreenSize = {
  breakpoint: BreakPoint;
  from: number;
  to: number;
};

type ScreenConfig = { [T in BreakPoint]: string };

const { pageWidth, pageHeight } = useBrowser();

export default () => {
  const rawWidth = ref<number>(pageWidth(1280) as number);
  const rawHeight = ref<number>(pageHeight(720) as number);

  const screenConfig: ScreenConfig = {
    xxs: '400px',
    xs: '500px',
    sm: '640px',
    md: '768px',
    xmd: '860px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1536px',
  } as ScreenConfig;

  const objectKeys: BreakPoint[] = Object.keys(screenConfig) as BreakPoint[];

  const keys: BreakPoint[] = [
    'xxxs',
    ...objectKeys.sort((a: BreakPoint, b: BreakPoint) =>
      parseInt(screenConfig[a], 10) > parseInt(screenConfig[b], 10) ? 1 : -1,
    ),
  ];

  const screenSizes = (): {
    breakpoint: BreakPoint;
    from: number;
    to: number;
  }[] =>
    keys.map((key, index): ScreenSize => {
      const nextKey = keys[index + 1];

      return {
        breakpoint: key,
        from: parseInt(screenConfig[key], 10) || 0,
        to: parseInt(screenConfig[nextKey], 10) - 1 || 9999,
      };
    });

  const currentBreakpoint = (): BreakPoint => {
    let breakpoint: BreakPoint = 'xxxs';

    screenSizes().forEach((size) => {
      if (rawWidth.value >= size.from && rawWidth.value <= size.to) {
        breakpoint = size.breakpoint;
      }
    });

    return breakpoint;
  };

  const detailsForBreakpoint = (
    breakpoint: BreakPoint,
  ): ScreenSize | undefined =>
    screenSizes().find((screenSize) => screenSize.breakpoint === breakpoint);

  const screenIsLessThanOrEqualTo = (breakpoint: BreakPoint): boolean =>
    rawWidth.value <= (detailsForBreakpoint(breakpoint)?.to || 0);

  const screenIsLessThan = (breakpoint: BreakPoint): boolean =>
    rawWidth.value < (detailsForBreakpoint(breakpoint)?.from || 0);

  const screenIs = (breakpoint: BreakPoint): boolean =>
    rawWidth.value >= (detailsForBreakpoint(breakpoint)?.from || 0) &&
    rawWidth.value <= (detailsForBreakpoint(breakpoint)?.to || 0);

  const screenIsGreaterThan = (breakpoint: BreakPoint): boolean =>
    rawWidth.value > (detailsForBreakpoint(breakpoint)?.to || 0);

  const screenIsGreaterThanOrEqualTo = (breakpoint: BreakPoint): boolean =>
    rawWidth.value >= (detailsForBreakpoint(breakpoint)?.from || 0);

  const isPortrait = (): boolean => {
    return rawWidth.value < rawHeight.value;
  };

  return {
    rawWidth,
    screenSizes,
    currentBreakpoint,
    screenIsLessThanOrEqualTo,
    screenIsLessThan,
    screenIs,
    screenIsGreaterThan,
    screenIsGreaterThanOrEqualTo,
    isPortrait,
  };
};
