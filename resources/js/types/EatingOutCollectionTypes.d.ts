import { HomeHoverItem } from '@/types/Types';

export type EateryCollectionCard = HomeHoverItem & {
  description: string;
  date: string;
  eateries_count?: number;
};

export type EateryCollectionPage = {
  id: number;
  title: string;
  image: string;
  published: string;
  updated: string | null;
  description: string;
  body: string;
};
