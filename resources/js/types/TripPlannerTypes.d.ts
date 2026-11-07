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

export type TripRoute = {
  geometry: string;
  distance: number;
  duration: number;
};

export type Trip = {
  key: string;
  points: TripLocation[];
  distance: number;
  stopInterval: number | null;
  route: TripRoute;
};

export type TripStop = {
  number: number;
  type: 'via' | 'suggested';
  label: string | null;
  duration: number;
  distanceAlongRoute: number;
  lat: number;
  lng: number;
};

export type TripSummary = {
  status: 'pending' | 'ready' | 'failed' | null;
  overview: string | null;
  stops: Record<number, string>;
};

export type TripTown = {
  name: string;
  link: string;
};
