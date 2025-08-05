import { Feature } from 'ol';

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
  loaded: boolean;
  new: boolean;
  lat: number;
  lng: number;
  element: Feature;
};
