import { ArticleFaq, HomeHoverItem } from '@/types/Types';
import { FeaturedInCollection } from '@/types/CollectionTypes';

export type RecipeDetailCard = HomeHoverItem & {
  square_image: string;
  description: string;
  date: string;
  features?: { feature: string }[];
  nutrition: {
    servings: string;
    calories: number;
    portion_size: number;
  };
};

export type RecipePage = {
  id: number;
  print_url: string;
  title: string;
  header_image_alt_text?: string;
  short_title?: string;
  image: string;
  square_image: string;
  published: string;
  updated: string;
  author: string;
  description: string;
  body?: string;
  ingredients: string;
  method: string;
  features: { feature: string; slug: string }[];
  allergens: { allergen: string; slug: string }[];
  timing: {
    prep_time: string;
    cook_time: string;
  };
  nutrition: RecipeDetailCard['nutrition'] & {
    carbs: number;
    fibre: number;
    fat: number;
    sugar: number;
    protein: number;
  };
  featured_in?: FeaturedInCollection[];
  faqs?: ArticleFaq[];
};

export type RecipeFeature = {
  feature: string;
  slug: string;
  recipes_count: number;
};

export type RecipeMeal = {
  meal: string;
  slug: string;
  recipes_count: number;
};

export type RecipeFreeFrom = {
  allergen: string;
  slug: string;
  recipes_count: number;
};

export type RecipeSetFilters = {
  features: string[];
  meals: string[];
  freeFrom: string[];
};
