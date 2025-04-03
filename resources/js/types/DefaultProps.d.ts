export type DefaultProps = {
  [x: string]: unknown;
  meta: MetaProps;
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
