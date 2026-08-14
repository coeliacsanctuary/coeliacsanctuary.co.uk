import { FeatureLike } from 'ol/Feature';
import { Fill, Icon, Stroke, Style, Text } from 'ol/style';
import CircleStyle from 'ol/style/Circle';

type ClusterSize = { radius: number; fontSize: number; ringWidth: number };

/** How far the soft outer halo extends beyond the disc itself. */
const haloWidth = 9;

const clusterSizeForCount = (count: number): ClusterSize => {
  if (count < 10) {
    return { radius: 16, fontSize: 13, ringWidth: 2 };
  }

  if (count < 25) {
    return { radius: 20, fontSize: 14, ringWidth: 2 };
  }

  if (count < 50) {
    return { radius: 24, fontSize: 15, ringWidth: 2.5 };
  }

  if (count < 100) {
    return { radius: 28, fontSize: 16, ringWidth: 3 };
  }

  return { radius: 32, fontSize: 17, ringWidth: 3 };
};

export const clusterStyle = (feature: FeatureLike): Style | Style[] => {
  const count: number = feature.get('point_count') as number;

  if (!count) {
    return markerStyle(
      feature.get('typeId') as number,
      feature.get('venueTypeId') as number | null,
    );
  }

  const { radius, fontSize, ringWidth } = clusterSizeForCount(count);

  const label: string = (
    (feature.get('point_count_abbreviated') as string | number | undefined) ??
    count
  ).toString();

  return [
    new Style({
      image: new CircleStyle({
        radius: radius + haloWidth,
        fill: new Fill({ color: 'rgba(0, 0, 0, 0.2)' }),
        displacement: [0, -2],
      }),
    }),

    new Style({
      image: new CircleStyle({
        radius: radius + haloWidth,
        fill: new Fill({ color: 'rgba(255, 255, 255, 0.6)' }),
      }),
    }),

    new Style({
      image: new CircleStyle({
        radius,
        fill: new Fill({ color: '#DBBC25' }),
        stroke: new Stroke({ color: '#fff', width: ringWidth }),
      }),
    }),

    new Style({
      text: new Text({
        text: label,
        font: `bold ${fontSize}px Raleway, ui-sans-serif`,
        fill: new Fill({ color: '#222' }),
      }),
    }),
  ];
};

const markerStyles = new Map<string, Style>();

export const markerStyle = (
  typeId: number,
  venueTypeId: number | null,
): Style => {
  const key = `${typeId}-${venueTypeId ?? ''}`;
  const cached = markerStyles.get(key);

  if (cached) {
    return cached;
  }

  const style = new Style({
    image: new Icon({
      scale: 0.36,
      /** The pin's point, rather than its middle, sits on the coordinate. */
      anchor: [0.5, 1],
      src: `/api/wheretoeat/marker/${typeId}${venueTypeId ? `/${venueTypeId}` : ''}`,
    }),
  });

  markerStyles.set(key, style);

  return style;
};

export const searchLocationMarkerStyle = (): Style =>
  new Style({
    image: new Icon({
      size: [60, 60],
      src: '/images/svg/marker.svg',
      color: '#000000',
    }),
  });
