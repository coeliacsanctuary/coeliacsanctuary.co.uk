import { useForm } from 'laravel-precognition-vue-inertia';
import eventBus from '@/eventBus';
import { VisitOptions } from '@inertiajs/core';
import { InertiaForm } from '@/types/Core';
import useGoogleEvents from '@/composables/useGoogleEvents';
import useLocalStorage from '@/composables/useLocalStorage';
import { router, usePage } from '@inertiajs/vue3';
import { ShopBasketItem, ShopHolidayProps } from '@/types/Shop';

type AddBasketPayload = {
  product_id: number;
  variant_id: number;
  quantity: number;
  include_add_on: boolean;
};

export type ShopHolidayConfirmation = {
  notice: string;
  storageKey: string;
  onConfirm: () => void;
};

export default () => {
  const page = usePage<{
    shopHoliday?: ShopHolidayProps;
    basket?: { items: ShopBasketItem[] };
  }>();

  const { isInLocalStorage } = useLocalStorage();

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

  const holidayStorageKey = (holiday: ShopHolidayProps): string =>
    `shop-holiday-${holiday.id}`;

  const holidayNeedsConfirming = (
    holiday: ShopHolidayProps | undefined,
  ): holiday is ShopHolidayProps => {
    if (!holiday) {
      return false;
    }

    if (!page.props.basket?.items.length) {
      return true;
    }

    return !isInLocalStorage(holidayStorageKey(holiday));
  };

  const sendAddBasketForm = (
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

  const submitAddBasketForm = (
    params: Partial<VisitOptions> = {},
    callback?: () => void,
  ) => {
    const holiday = page.props.shopHoliday;

    if (holidayNeedsConfirming(holiday)) {
      eventBus.$emit<ShopHolidayConfirmation>('confirm-shop-holiday', {
        notice: holiday.notice,
        storageKey: holidayStorageKey(holiday),
        onConfirm: () => sendAddBasketForm(params, callback),
      });

      return;
    }

    sendAddBasketForm(params, callback);
  };

  return { addBasketForm, prepareAddBasketForm, submitAddBasketForm };
};
