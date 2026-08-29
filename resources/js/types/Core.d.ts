import 'vite/client';
import { VisitOptions } from '@inertiajs/core';
import { InertiaForm as BaseInertiaForm } from '@inertiajs/vue3';
import { Component, DefineComponent } from 'vue';

export {};

declare global {
  interface Window {
    gtag: (key: string, event: string, attributes?: object) => void;
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

export type InertiaForm<T extends object> = BaseInertiaForm<T> & {
  submit(options?: Partial<VisitOptions>): void;
  validate(field: keyof T): void;
  errors: Partial<T>;
};
