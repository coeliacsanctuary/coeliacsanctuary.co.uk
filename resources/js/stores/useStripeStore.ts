import { defineStore } from 'pinia';
import { loadStripe, Stripe, StripeElements } from '@stripe/stripe-js';

type State = {
  instantiated: boolean;
  stateStripe: Stripe;
  stateElements: StripeElements;
};

type Getters = {
  isReady: (state: State) => boolean;
  stripe: (state: State) => Stripe;
  elements: (state: State) => StripeElements;
};

type Actions = {
  instantiate: (paymentIntentId: string) => Promise<void>;
  reset: () => void;
};

const useStripeStore = defineStore<'stripe', State, Getters, Actions>(
  'stripe',
  {
    state: () => ({
      instantiated: false,
      stateStripe: {} as Stripe,
      stateElements: {} as StripeElements,
    }),
    getters: {
      isReady: (state) => state.instantiated,
      stripe: (state) => state.stateStripe,
      elements: (state) => state.stateElements,
    },
    actions: {
      reset() {
        this.stateStripe = {} as Stripe;
        this.stateElements = {} as StripeElements;
        this.instantiated = false;
      },
      async instantiate(clientSecret: string) {
        if (this.instantiated) {
          return;
        }

        const key: string = import.meta.env.VITE_STRIPE_PUBLIC_KEY as string;

        this.stateStripe = (await loadStripe(key)) as Stripe;
        this.stateElements = this.stateStripe.elements({
          clientSecret,
          appearance: {
            variables: {
              colorPrimary: '#80CCFC',
            },
          },
        });

        this.instantiated = true;
      },
    },
  },
);

export default useStripeStore;
