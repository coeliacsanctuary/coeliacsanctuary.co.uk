import { HomeHoverItem } from '@/types/Types';
import { FeaturedInCollection } from '@/types/CollectionTypes';

export type BlogDetailCard = HomeHoverItem & {
  description: string;
  date: string;
  comments_count?: number;
  tags?: BlogTag[];
};

export type BlogPage = {
  id: number;
  title: string;
  image: string;
  published: string;
  updated: string | null;
  description: string;
  body: string;
  hasTwitterEmbed: boolean;
  tags: BlogTag[];
  featured_in?: FeaturedInCollection[];
};

export type BlogTag = {
  tag: string;
  slug: string;
};

export type BlogTagCount = {
  slug: string;
  tag: string;
  blogs_count: number;
};
