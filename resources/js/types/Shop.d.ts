import { StarRating } from '@/types/EateryTypes';
import { ArticleFaq } from '@/types/Types';

export type ShopCategoryIndex = {
  title: string;
  description: string;
  link: string;
  image: string;
  travelCardSearch: boolean;
  products_count?: number;
  price?: string | null;
  reviews_count?: number;
};

export type ShopPopularProduct = {
  title: string;
  link: string;
  image: string;
  price: string;
  rating?: {
    average: StarRating;
    count: number;
  };
};

export type ShopIndexReview = {
  name: string | null;
  review: string;
  rating: StarRating;
  product: {
    title: string;
    link: string;
  } | null;
};

export type ShopBaseProduct = {
  title: string;
  description: string;
  image: string;
  additional_images?: string[];
  rating?: {
    average: StarRating;
    count: number;
  };
};

export type ShopProductIndex = ShopBaseProduct & {
  id: number;
  link: string;
  price: string;
  in_stock: boolean;
  number_of_variants: number;
  primary_variant: number;
  primary_variant_quantity: number;
  footnote: string | null;
};

export type ShopProductDetail = ShopBaseProduct & {
  id: number;
  long_description: string;
  prices: ShopProductPrice;
  variant_title: string;
  variants: ShopProductVariant[];
  category: {
    title: string;
    link: string;
  };
  rating?: ShopProductRating;
  add_ons?: ShopProductAddOn;
  faqs: ArticleFaq[] | null;
};

export type ShopProductAddOn = {
  name: string;
  description: string;
  price: ShopProductPrice;
};

export type ShopProductPrice = {
  current_price: string;
  raw_price: number;
  old_price?: string;
};

export type ShopTravelCardProductDetail = ShopProductDetail & {
  is_travel_card: true;
  countries: {
    language: string;
    countries: { country: string; code?: string }[];
  }[];
};

export type ShopProductVariant = {
  id: number;
  title: string;
  description?: string;
  icon?: {
    component: string;
    color: string;
  };
  quantity: number;
};

export type ShopProductRating = {
  average: StarRating;
  count: number;
  breakdown: {
    rating: StarRating;
    count: number;
  }[];
};

export type ShopProductReview = {
  name: string;
  review: string;
  rating: StarRating;
  date: string;
  date_diff: string;
};

export type ShopBasketItem = {
  id: number;
  title: string;
  description?: string;
  add_on?: {
    title: string;
    description: string;
    price: string;
    in_basket: boolean;
  };
  link: string;
  variant: string;
  item_price: string;
  line_price: string;
  quantity: number;
  image: string;
};

export type CheckoutContactStep = {
  name: string;
  email: string;
  phone?: string;
  subscribeToNewsletter: boolean;
};

export type CheckoutShippingStep = {
  address_1: string;
  address_2?: string;
  address_3?: string;
  town: string;
  county: string;
  postcode: string;
};

export type CheckoutBillingStep = CheckoutShippingStep & {
  name: string;
  country: string;
};

export type CheckoutForm = {
  contact: CheckoutContactStep;
  shipping: CheckoutShippingStep;
  billing: CheckoutBillingStep;
};

export type CheckoutFormErrors = CheckoutForm & {
  basket: string;
};

export type OrderCompleteProps = {
  id: string;
  subtotal: string;
  postage: string;
  total: string;
  shipping: string[];
  fees: { fee: string; description?: string }[];
  total_fees: string;
  discount: null | { amount: string; name: string };
  products: ShopBasketItem[];
  payment: CardPayment | PaypalPayment;
  has_add_ons: boolean;
  event: {
    [T: string]: string | number | [];
  };
};

type CardPayment = {
  type: 'Card' | 'Google Pay' | 'Apple Pay' | 'Samsung Pay';
  expiry?: string;
  lastDigits?: string;
};

type PaypalPayment = {
  type: 'PayPal';
  paypalAccount?: string;
};

export type TravelCardDestinationChip = {
  term: string;
  flag: string | null;
};

export type TravelCardDestination = {
  term: string;
  type: string;
  flag: string | null;
  products: ShopProductIndex[];
};

export type TravelCardSearchResult = {
  term: string;
  destinations: TravelCardDestination[];
  covers_all: ShopProductIndex[];
};

export type TravelCardReviewSummary = {
  reviews: ShopIndexReview[];
  count: number;
  average: number | null;
};

export type TravelCardLookupResult = {
  id: number | null;
  term: string;
  value: string;
  type: string;
};

export type ShopHolidayProps = {
  id: number;
  notice: string;
};
