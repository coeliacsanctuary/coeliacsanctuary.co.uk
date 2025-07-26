import 'vite/client';
import { VisitOptions } from '@inertiajs/core';
import { InertiaForm as BaseInertiaForm } from '@inertiajs/vue3';
import { Component, DefineComponent } from 'vue';
import { RequestMethod, ValidationConfig } from 'laravel-precognition';
import '@types/google.maps';

export {};

declare global {
  interface Window {
    gtag: (key: string, event: string, attributes: object = {}) => void;
    adsbygoogle?: {
      loaded: boolean;
      push: (args: unknown) => void;
    };
    __abg_called?: boolean;
  }
}

export type InertiaPage = DefineComponent & {
  default: {
    layout?: Component;
  };
};

declare module 'v-click-outside';

export type InertiaForm<T> = BaseInertiaForm<T> & {
  submit(options?: Partial<VisitOptions>): void;
  validate(field: keyof T): void;
  errors: Partial<T>;
};

declare module 'laravel-precognition-vue-inertia' {
  export declare const useForm: <Data extends Record<string, unknown>>(
    method: RequestMethod | (() => RequestMethod),
    url: string | (() => string),
    inputs: Data,
    config?: ValidationConfig,
  ) => InertiaForm<Data>;
}
