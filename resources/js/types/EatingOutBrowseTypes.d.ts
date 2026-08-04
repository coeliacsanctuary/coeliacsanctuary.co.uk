export type UrlParameters = {
  latLng?: string;
  zoom?: string;
  categories?: string;
  venueTypes?: string;
  features?: string;
};

export type MarkerProps = {
  id: string;
  color: string;
};

export type Marker = MarkerProps & {
  lat: number;
  lng: number;
};
