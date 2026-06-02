import { HomeHoverItem } from '@/types/Types';
import { EateryLocation, StarRating } from '@/types/EateryTypes';

export type CollectionDetailCard = HomeHoverItem & {
  description: string;
  date: string;
  number_of_items: number;
};

export type CollectionPage = {
  id: number;
  title: string;
  image: string;
  header_image_alt_text?: string;
  published: string;
  updated: string;
  author: string;
  description: string;
  body?: string;
  groups: CollectionGroup[];
};

export type CollectionGroup = {
  title?: string;
  body?: string;
  items: CollectionItem[];
};

export type CollectableType = 'Blog' | 'Recipe' | 'Eatery' | 'NationwideBranch';

export type CollectionItem = {
  type: CollectableType;
  link: string;
  [T: string]: unknown;
};

export type BlogCollectionItem = CollectionItem & {
  type: 'Blog';
  title: string;
  description: string;
  date: string;
  image: string;
  header_image_alt_text?: string;
};

export type RecipeCollectionItem = CollectionItem & {
  type: 'Recipe';
  title: string;
  description: string;
  date: string;
  image: string;
  header_image_alt_text?: string;
  square_image?: string;
};

export type EateryCollectionItem = CollectionItem & {
  type: 'Eatery' | 'NationwideBranch';
  name: string;
  full_location: string;
  description: string;
  location: EateryLocation;
  reviews: {
    number: number;
    average: StarRating;
  };
};

export type HomepageCollection = {
  title: string;
  description: string;
  link: string;
  items_to_display: 1 | 2 | 3 | 4 | 6 | 8;
  items: HomepageCollectedItem[];
};

export type HomepageCollectedItem = {
  type: 'Blog' | 'Recipe';
  title: string;
  image: string;
  header_image_alt_text?: string;
  square_image?: string;
  link: string;
};

export type FeaturedInCollection = {
  title: string;
  link: string;
  description: string;
  image: string;
  header_image_alt_text?: string;
};
