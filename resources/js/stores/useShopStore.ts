import { defineStore } from 'pinia';
import {
  CheckoutBillingStep,
  CheckoutContactStep,
  CheckoutForm,
  CheckoutFormErrors,
  CheckoutShippingStep,
} from '@/types/Shop';

type Keys = keyof CheckoutForm;

type State = {
  data: CheckoutForm;
  country: string;
  errors: Partial<CheckoutFormErrors>;
};

type Getters = {
  toForm: (state: State) => CheckoutForm;
  getErrors: (state: State) => Partial<CheckoutFormErrors>;
  customerName: (state: State) => string;
  userDetails: (state: State) => CheckoutContactStep;
  shippingDetails: (state: State) => CheckoutShippingStep;
  billingDetails: (state: State) => CheckoutBillingStep;
  selectedCountry: (state: State) => string;
};

type Actions = {
  setForm: (state: Partial<CheckoutForm>) => void;
  setUserDetails: (state: CheckoutContactStep) => void;
  setShippingDetails: (state: CheckoutShippingStep) => void;
  setBillingDetails: (state: CheckoutBillingStep) => void;
  setErrors: (state: Partial<CheckoutFormErrors>) => void;
  setCountry: (country: string) => void;
};

const useShopStore = defineStore<'shop-checkout', State, Getters, Actions>(
  'shop-checkout',
  {
    state: () => ({
      data: {
        contact: {
          name: '',
          email: '',
          email_confirmation: '',
          phone: '',
          subscribeToNewsletter: false,
        },

        shipping: {
          address_1: '',
          address_2: '',
          address_3: '',
          town: '',
          county: '',
          postcode: '',
        },

        billing: {
          name: '',
          address_1: '',
          address_2: '',
          address_3: '',
          town: '',
          county: '',
          postcode: '',
          country: '',
        },
      },

      country: '',

      errors: {},
    }),
    getters: {
      toForm: (state) => state.data,
      getErrors: (state) => state.errors,
      customerName: (state) => state.data.contact.name,
      userDetails: (state) => state.data.contact,
      shippingDetails: (state) => state.data.shipping,
      billingDetails: (state) => state.data.billing,
      selectedCountry: (state) => state.country,
    },
    actions: {
      setErrors(state) {
        this.errors = { ...state };
      },
      setForm(state) {
        (<Keys[]>Object.keys(state)).forEach((key: Keys) => {
          const value:
            | CheckoutContactStep
            | CheckoutBillingStep
            | CheckoutShippingStep = state[key] as
            | CheckoutContactStep
            | CheckoutBillingStep
            | CheckoutShippingStep;

          (<CheckoutContactStep | CheckoutBillingStep | CheckoutShippingStep>(
            this.data[key]
          )) = value;
        });
      },
      setUserDetails(details) {
        this.setForm({ contact: details });
      },
      setShippingDetails(details) {
        this.setForm({ shipping: details });
      },
      setBillingDetails(details) {
        if (
          ['england', 'wales', 'scotland'].includes(
            details.country.toLowerCase(),
          )
        ) {
          details.country = 'united kingdom';
        }

        this.setForm({ billing: details });
      },
      setCountry(country: string) {
        this.country = country;
      },
    },
  },
);

export default useShopStore;
