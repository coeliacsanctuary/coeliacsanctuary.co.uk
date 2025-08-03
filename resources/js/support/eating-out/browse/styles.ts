import { FeatureLike } from 'ol/Feature';
import { Fill, Icon, Stroke, Style, Text } from 'ol/style';
import CircleStyle from 'ol/style/Circle';

export const clusterStyle = (feature: FeatureLike) => {
  const count: number = feature.get('point_count') as number;

  if (count) {
    return new Style({
      image: new CircleStyle({
        radius: 20,
        fill: new Fill({ color: '#ecd14a' }),
        stroke: new Stroke({ color: '#000' }),
      }),
      text: new Text({
        text: count.toString(),
        fill: new Fill({ color: '#000' }),
        scale: 2,
      }),
    });
  }

  return markerStyle(feature.get('color') as string);
};

export const markerStyle = (color: string): Style =>
  new Style({
    image: new Icon({
      size: [50, 50],
      src: '/images/svg/marker.svg',
      color,
    }),
  });
