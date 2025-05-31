export type MapProps = {
  lat: number;
  lng: number;
  zoom?: number;
};

export const MapPropDefaults: Partial<MapProps> = {
  zoom: 16,
};

export type MapModalProps = MapProps & {
  title?: string;
};

export const MapModalPropDefaults = {
  ...MapPropDefaults,
  title: undefined,
};

export type StaticMapProps = MapProps & {
  title?: string;
  mapClasses?: string;
  canExpand?: boolean;
  additionalParams?: Record<string, string>;
};

export const StaticMapPropDefaults: Partial<StaticMapProps> = {
  ...MapPropDefaults,
  title: undefined,
  mapClasses:
    'min-h-map-small md:max-lg:min-h-map lg:max-xl:min-h-map-small xl:min-h-map',
  canExpand: true,
  additionalParams: undefined,
};
