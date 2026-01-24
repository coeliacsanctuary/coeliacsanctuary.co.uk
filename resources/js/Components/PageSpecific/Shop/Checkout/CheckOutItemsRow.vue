<script setup lang="ts">
import { ShopBasketItem } from '@/types/Shop';
import { TrashIcon } from '@heroicons/vue/24/outline';
import Loader from '@/Components/Loader.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import { Link, router } from '@inertiajs/vue3';
import QuantitySwitcher from '@/Components/PageSpecific/Shop/Checkout/QuantitySwitcher.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ref, watch } from 'vue';
import eventBus from '@/eventBus';

const props = defineProps<{ item: ShopBasketItem }>();

const isLoading = ref(false);
const hasError = ref(false);
const isDeletingItem = ref(false);
const isRemovingAddon = ref(false);
const includeAddOn = ref(props.item.add_on?.in_basket ?? false);

const alterQuantity = (action: 'increase' | 'decrease') => {
  isLoading.value = true;
  hasError.value = false;

  router.patch(
    '/shop/basket',
    {
      action,
      item_id: props.item.id,
    },
    {
      preserveScroll: true,
      only: ['basket', 'has_basket', 'payment_intent'],
      onFinish: () => {
        isLoading.value = false;
        eventBus.$emit('refresh-payment-element');
      },
      onError: (e) => {
        if (e?.quantity) {
          hasError.value = true;
        }
      },
    },
  );
};

const removeItem = () => {
  isDeletingItem.value = true;

  router.delete(`/shop/basket/${props.item.id}`, {
    preserveScroll: true,
    only: ['basket', 'has_basket', 'payment_intent'],
    onSuccess: () => {
      eventBus.$emit('refresh-payment-element');
    },
  });
};

const removeAddOn = () => {
  if (!props.item.add_on) {
    return;
  }

  isLoading.value = true;

  router.delete(`/shop/basket/${props.item.id}/add-on`, {
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

const addAddOn = () => {
  if (!props.item.add_on) {
    return;
  }

  isLoading.value = true;

  router.post(
    `/shop/basket/${props.item.id}/add-on`,
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false;
      },
    },
  );
};

watch(includeAddOn, () => {
  if (includeAddOn.value) {
    addAddOn();

    return;
  }

  removeAddOn();
});
</script>

<template>
  <li class="relative">
    <Loader
      :display="isLoading"
      absolute
      on-top
      blur
      color="secondary"
      size="size-12"
      width="border-8"
    />

    <div class="flex space-x-3 py-3">
      <div
        class="h-17 w-17 shrink-0 overflow-hidden rounded-md border border-gray-200 xs:h-20 xs:w-20 sm:h-24 sm:w-24"
      >
        <img
          :src="item.image"
          :alt="item.title"
          class="h-full w-full object-cover object-center"
        />
      </div>

      <div class="flex flex-1 flex-col">
        <div>
          <div class="flex justify-between text-base">
            <h3>
              <Link
                :href="item.link"
                class="font-semibold hover:text-primary-dark"
              >
                {{ item.title }}
                <template v-if="item.variant !== ''">
                  - {{ item.variant }}
                </template>
              </Link>
            </h3>
            <p
              class="ml-4 text-xl font-semibold"
              v-text="item.line_price"
            />
          </div>

          <p
            v-if="item.description"
            class="mt-1 text-sm text-gray-500"
            v-text="item.description"
          />
        </div>

        <div class="flex flex-1 items-center justify-between">
          <div class="flex flex-1 items-center space-x-1">
            <p>Quantity</p>

            <QuantitySwitcher
              :quantity="item.quantity"
              @alter="(mode) => alterQuantity(mode)"
            />
          </div>

          <CoeliacButton
            theme="faded"
            icon-only
            :icon="TrashIcon"
            size="xxl"
            as="button"
            type="button"
            classes="p-1! hover:text-primary-dark !shadow-none"
            :loading="isDeletingItem"
            @click="removeItem()"
          />
        </div>

        <span
          v-if="hasError"
          class="text-sm font-semibold text-red"
        >
          Sorry, there isn't enough quantity available...
        </span>
      </div>
    </div>

    <div
      v-if="item.add_on"
      class="-my-2 flex space-x-3 pb-3"
    >
      <div
        class="flex w-17 shrink-0 items-baseline justify-end xs:w-20 sm:w-24"
      >
        <FormCheckbox
          v-model="includeAddOn"
          name="include_add_on"
          class="py-0!"
          label=""
          hide-label
          xl
        />
      </div>

      <div class="flex flex-1 flex-col">
        <div>
          <div class="flex justify-between text-base">
            <div class="flex flex-col">
              <h3 v-text="item.add_on.title" />
              <p
                class="text-sm"
                v-text="item.add_on.description"
              />
            </div>

            <p
              class="ml-4 flex-shrink-0"
              :class="includeAddOn ? 'text-xl font-semibold' : 'text-sm'"
              v-text="`+${item.add_on.price}`"
            />
          </div>
        </div>
      </div>
    </div>
  </li>
</template>
