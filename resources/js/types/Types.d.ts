import { Link } from '@inertiajs/vue3';
import {
  Component,
  FunctionalComponent,
  HTMLAttributes,
  VNodeProps,
} from 'vue';

export type HomeHoverItem = {
  title: string;
  link: string;
  image: string;
  square_image?: string;
  type?: 'Blog' | 'Recipe';
};

export type FormItem = {
  value: string | number;
  label: string;
};

export type SelectBoxItem = FormItem & {
  disabled?: boolean;
};

export type CheckboxItem = SelectBoxItem & {
  checked?: boolean;
  groupBy?: string;
  originalIndex?: number;
};

export type Comment = {
  name: string;
  comment: string;
  published: string;
  reply?: CommentReply;
};

export type CommentReply = {
  comment: string;
  published: string;
};

export type HeadingBackLink = {
  label: string;
  href: string;
  position?: 'top' | 'bottom';
  direction?: 'left' | 'center' | 'right';
};

export type HeadingCustomLink = HeadingBackLink & {
  classes: string | string[];
  icon?: CustomComponent;
  iconPosition?: 'left' | 'right';
  newTab?: boolean;
};

export type CoeliacButtonProps = {
  label?: string;
  theme?: 'primary' | 'faded' | 'secondary' | 'light' | 'negative';
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'xxl';
  bold?: boolean;
  as?: typeof Link | 'button' | 'a';
  type?: 'submit' | 'button';
  href?: string;
  icon?: CustomComponent;
  iconPosition?: 'left' | 'right' | 'center';
  loading?: boolean;
  classes?: string;
  disabled?: boolean;
  iconOnly?: boolean;
  target?: string;
  iconClasses?: string;
};

export type CustomComponent =
  | string
  | false
  | FunctionalComponent<HTMLAttributes & VNodeProps>
  | Component
  | Record<string, unknown>
  | (() => void);
