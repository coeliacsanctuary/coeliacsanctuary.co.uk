export type UrlParameters = {
  latLng?: string;
  zoom?: string;
  categories?: string;
  venueTypes?: string;
  features?: string;
};

export type MarkerProps = {
  id: string;
  typeId: number;
  venueTypeId: number | null;
};

export type Marker = MarkerProps & {
  lat: number;
  lng: number;
};
