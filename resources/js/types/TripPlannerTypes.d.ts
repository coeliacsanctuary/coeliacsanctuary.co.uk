import { EateryBrowseResource, LatLng } from '@/types/EateryTypes';

export type TripEatery = EateryBrowseResource & {
  id: number;
  branchId: number | null;
  name: string | null;
  town: string | null;
  chain: string | null;
  rating: number | null;
  reviewsCount: number;
  distanceAlongRoute: number;
  distanceFromRoute: number;
  durationAlongRoute: number;
};

export type TripLocation = LatLng & { label: string };

export type TripRoute = { geometry: string; distance: number; duration: number };

export type Trip = {
  key: string;
  points: TripLocation[];
  distance: number;
  stopInterval: number | null;
  route: TripRoute;
};

export type TripStop = {
  number: number;
  duration: number;
  distanceAlongRoute: number;
  lat: number;
  lng: number;
};
