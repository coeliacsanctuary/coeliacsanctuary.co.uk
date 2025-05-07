import { StarRating } from '@/types/EateryTypes';
import { CustomComponent } from '@/types/Types';

export type ShopCategoryIndex = {
  title: string;
  description: string;
  link: string;
  image: string;
  travelCardSearch: boolean;
};

export type ShopBaseProduct = {
  title: string;
  description: string;
  image: string;
  rating?: {
    average: StarRating;
    count: number;
  };
};

export type ShopProductIndex = ShopBaseProduct & {
  id: number;
  link: string;
  price: string;
  number_of_variants: number;
  primary_variant: number;
  primary_variant_quantity: number;
};

export type ShopProductDetail = ShopBaseProduct & {
  id: number;
  long_description: string;
  prices: {
    current_price: string;
    old_price?: string;
  };
  variant_title: string;
  variants: ShopProductVariant[];
  category: {
    title: string;
    link: string;
  };
  rating?: ShopProductRating;
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
  email_confirmation: string;
  phone?: string;
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
  discount: null | { amount: string; name: string };
  products: ShopBasketItem[];
  payment: CardPayment | PaypalPayment;
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

export type TravelCardFeedbackItem = {
  review: string;
  name: string;
  product: string;
  link: string;
};

export type ProductAdditionalDetailAccordionProps = {
  title: string;
  openByDefault?: boolean;
  content?: string;
  component?: CustomComponent;
  props?: Record<string, unknown>;
  headerComponent?: CustomComponent;
  headerClasses?: string;
  wrapperComponent?: CustomComponent;
  wrapperClasses?: string;
  panelClasses?: string;
};
