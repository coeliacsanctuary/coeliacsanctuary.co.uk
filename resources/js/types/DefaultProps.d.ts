export type DefaultProps = {
  [x: string]: unknown;
  is_admin?: true;
  meta: MetaProps;
  popup?: PopupProps;
  announcement?: AnnouncementProps;
  errors: import('@inertiajs/core').Errors & import('@inertiajs/core').ErrorBag;
};

export type MetaProps = {
  baseUrl: string;
  currentUrl: string;
  title: string;
  description: string;
  tags: string[];
  image: string;
  schema?: string[];
  doNotTrack?: true;
  feed?: string;
  alternateMetas?: {
    [T: string]: string;
  };
};

export type PopupProps = {
  id: number;
  text: string;
  link: string;
  primary_image: string;
  secondary_image?: string;
};

export type AnnouncementProps = {
  title: string;
  text: string;
};
