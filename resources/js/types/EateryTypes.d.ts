import { CheckboxItem } from '@/types/Types';
import { DefineComponent } from 'vue';

export type County = {
  name: string;
  title: string;
  link: string;
};

export type CountyPage = County & {
  latlng: string;
  image?: string;
  eateries: number;
  reviews: number;
  towns: CountyPageTown[];
};

export type NationwidePage = County & {
  eateries: number;
  reviews: number;
  chains: NationwideEatery[];
};

export type CountyPageTown = Town & {
  link: string;
  eateries: number;
  attractions: number;
  hotels: number;
};

export type NationwideEatery = {
  cuisine?: string;
  info: string;
  key: number;
  link: string;
  name: string;
  phone?: string;
  reviews: {
    number: number;
    average: string;
  };
  type: string;
  venue_type?: string;
  website?: string;
};

export type Eatery = {
  name: string;
  link: string;
  county: {
    id: number;
    name: string;
    link: string;
  };
  town: {
    id: number;
    name: string;
    link: string;
  };
  info: string;
};

export type CountyEatery = Eatery & {
  address: string;
  rating: StarRating;
  rating_count: number;
};

export type Town = {
  name: string;
  county: County;
};

export type TownPage = Town & {
  latlng: string;
  link: string;
  image?: string;
};

export type EateryFilterItem = CheckboxItem & {
  value: string;
  label: string;
};

export type EateryFilterKeys = 'categories' | 'venueTypes' | 'features';

export type EateryFilters = {
  [T in EateryFilterKeys]: EateryFilterItem[];
};

export type TownEatery = Eatery & {
  key: string;
  link: string;
  type: string;
  venue_type?: string;
  cuisine?: string;
  website?: string;
  restaurants: {
    name?: string;
    info: string;
  }[];
  location: EateryLocation;
  phone?: string;
  reviews: {
    number: number;
    average: StarRating;
  };
  isNationwideBranch?: boolean;
  branch?: EateryNationwideBranch;
  distance?: number;
};

export type EateryLocation = LatLng & {
  address: string;
};

export type EateryNationwideBranch = {
  id: number;
  name?: string;
  location: EateryLocation;
  link: string;
  county: {
    id: number;
    name: string;
    link: string;
  };
  town: {
    id: number;
    name: string;
    link: string;
  };
};

export type DetailedEatery = Exclude<TownEatery, 'key'> & {
  id: number;
  closed_down: boolean;
  menu?: string;
  reviews: {
    number: number;
    average: string;
    expense?: {
      value: string;
      label: string;
    };
    has_rated: boolean;
    images?: ReviewImage[];
    admin_review?: Exclude<EateryReview, ['id', 'name']>;
    user_reviews: EateryReview[];
    ratings: {
      rating: StarRating;
      count: number;
    }[];
  };
  features?: {
    name: string;
    slug: string;
  }[];
  opening_times?: {
    is_open_now: boolean;
    today: OpeningTime;
    days: {
      [T in Days]: OpeningTime;
    };
  };
  last_updated: string;
  last_updated_human: string;
  is_nationwide: boolean;
  nationwide_branches?: EateryBranchesCollection;
};

export type EateryBranchesCollection = {
  [Country: string]: {
    [County: string]: {
      [Town: string]: EateryNationwideBranch[];
    };
  };
};

export type EateryBrowseDetails = Exclude<
  DetailedEatery,
  | 'menu'
  | 'reviews'
  | 'features'
  | 'opening_times'
  | 'isNationwideBranch'
  | 'branch'
  | 'details'
> & {
  reviews: {
    number: number;
    average: StarRating;
  };
  full_location: string;
};

export type Days =
  | 'monday'
  | 'tuesday'
  | 'wednesday'
  | 'thursday'
  | 'friday'
  | 'saturday'
  | 'sunday';

export type OpeningTime = {
  opens: string;
  closes: string;
};

export type EateryReview = {
  id: string;
  published: string;
  date_diff: string;
  body?: string;
  name?: string;
  rating: StarRating;
  expense?: {
    value: string;
    label: string;
  };
  food_rating?: string;
  service_rating?: string;
  branch_name?: string;
  images: ReviewImage[];
};

export type ReviewImage = {
  id: string;
  thumbnail: string;
  path: string;
};

export type StarRating =
  | 0
  | '0'
  | 0.5
  | '0.5'
  | 1
  | '1'
  | 1.5
  | '1.5'
  | 2
  | '2'
  | 2.5
  | '2.5'
  | 3
  | '3'
  | 3.5
  | '3.5'
  | 4
  | '4'
  | 4.5
  | '4.5'
  | 5
  | '5';

export type EditableEateryData = {
  address: string;
  website?: string;
  gf_menu_link?: string;
  phone?: string;
  type_id: number;
  venue_type: EditableEaterySelectableData;
  cuisine: EditableEaterySelectableData;
  opening_times: {
    [T in Days | 'today']: [null, null] | [string, string];
  };
  features: {
    selected: {
      id: number;
      label: string;
    }[];
    values: EditableEateryFeatureValues[];
  };
  is_nationwide: boolean;
};

type EditableEaterySelectableData = {
  id: number;
  label: string;
  values: {
    value: number;
    label: string;
    selected: boolean;
  }[];
};

type EditableEateryFeatureValues = {
  id: number;
  label: string;
  selected: boolean;
};

type BaseEditableEateryField = {
  id: string;
  label: string;
  shouldDisplay: boolean;
  getter: () => string | null;
  capitalise?: boolean;
  truncate?: boolean;
  isFormField: boolean;
  updated: boolean;
};

type BaseEditableEateryFieldForm = BaseEditableEateryField & {
  isFormField: true;
  formField: EditableEateryFieldComponent & {
    value: () => string | number;
  };
};

type BaseEditableEateryFieldComponent = BaseEditableEateryField & {
  isFormField: false;
  component: EditableEateryFieldComponent & {
    change: (value: object[]) => void;
  };
};

export type EditableEateryField =
  | BaseEditableEateryFieldForm
  | BaseEditableEateryFieldComponent;

type EditableEateryFieldComponent = {
  component: DefineComponent;
  props?: Partial<Record<string, unknown>>;
};

export type EateryCountryListProp = {
  [T: string]: EateryCountryPropItem;
};

export type EateryCountryPropItem = {
  list: EateryCountryList[];
  counties: number;
  eateries: number;
};

export type EateryCountryList = {
  name: string;
  slug: string;
  eateries: number;
  branches: number;
  attractions: number;
  hotels: number;
  total: number;
};

export type EaterySimpleReviewResource = {
  rating: StarRating;
  eatery: EaterySimpleHomeResource;
  created_at: string;
};

export type EaterySimpleHomeResource = {
  name: string;
  link: string;
  location: {
    name: string;
    link: string;
  };
  address: string;
  created_at: string;
};

export type LatLng = {
  lat: number;
  lng: number;
};

export type EateryBrowseResource = {
  key: string;
  isNationwideBranch: boolean;
  location: LatLng;
  color: string;
};
