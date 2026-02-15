import { useForm } from 'laravel-precognition-vue-inertia';
import eventBus from '@/eventBus';
import { VisitOptions } from '@inertiajs/core';
import { InertiaForm } from '@/types/Core';
import useGoogleEvents from '@/composables/useGoogleEvents';
import { router } from '@inertiajs/vue3';

type AddBasketPayload = {
  product_id: number;
  variant_id: number;
  quantity: number;
  include_add_on: boolean;
};

export default () => {
  const addBasketForm = useForm<Partial<AddBasketPayload>>(
    'put',
    '/shop/basket',
    {
      product_id: undefined,
      variant_id: undefined,
      quantity: 1,
      include_add_on: false,
    },
  ) as InertiaForm<Partial<AddBasketPayload>>;

  const prepareAddBasketForm = (
    productId: number,
    variantId: number,
    quantity: number = 1,
    includeAddOn: boolean = false,
  ) => {
    addBasketForm.product_id = productId;
    addBasketForm.variant_id = variantId;
    addBasketForm.quantity = quantity;
    addBasketForm.include_add_on = includeAddOn;
  };

  const submitAddBasketForm = (
    params: Partial<VisitOptions> = {},
    callback?: () => void,
  ) => {
    addBasketForm.submit({
      ...params,
      preserveScroll: true,
      onSuccess: () => {
        eventBus.$emit('product-added-to-basket');
        router.flushAll();

        useGoogleEvents().googleEvent('event', 'add_to_cart', {
          items: [
            {
              productId: addBasketForm.product_id,
              variantId: addBasketForm.variant_id,
              quantity: addBasketForm.quantity,
            },
          ],
        });

        if (callback) {
          callback();
        }
      },
    });
  };

  return { addBasketForm, prepareAddBasketForm, submitAddBasketForm };
};
