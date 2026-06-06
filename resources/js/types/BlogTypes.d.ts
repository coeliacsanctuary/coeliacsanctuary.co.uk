import { ArticleFaq, HomeHoverItem } from '@/types/Types';
import { FeaturedInCollection } from '@/types/CollectionTypes';

export type BlogSimpleCard = Exclude<HomeHoverItem, 'type' & 'square_image'>;

export type RelatedBlogSimpleCard = BlogSimpleCard & {
  related_tag: string;
  related_tag_url: string;
};

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
  header_image_alt_text?: string;
  published: string;
  updated: string | null;
  description: string;
  body: string;
  hasTwitterEmbed: boolean;
  short_title?: string;
  show_author: boolean;
  tags: BlogTag[];
  featured_in?: FeaturedInCollection[];
  faqs?: ArticleFaq[];
  faq_display?: 'top' | 'bottom';
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
