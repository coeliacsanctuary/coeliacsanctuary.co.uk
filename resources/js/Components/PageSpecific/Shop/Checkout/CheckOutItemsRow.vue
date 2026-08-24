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

    <div class="flex gap-3 py-4 sm:gap-4">
      <div
        class="size-20 shrink-0 overflow-hidden rounded-lg border border-primary-light/60 sm:size-24"
      >
        <img
          :src="item.image"
          :alt="item.title"
          class="h-full w-full object-cover object-center"
        />
      </div>

      <div class="flex flex-1 flex-col gap-2">
        <div>
          <div class="flex justify-between gap-3 text-base">
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
              class="shrink-0 text-xl font-semibold"
              v-text="item.line_price"
            />
          </div>

          <p
            v-if="item.description"
            class="mt-1 text-sm text-grey-dark"
            v-text="item.description"
          />
        </div>

        <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2">
          <QuantitySwitcher
            size="lg"
            :quantity="item.quantity"
            @alter="(mode) => alterQuantity(mode)"
          />

          <CoeliacButton
            theme="faded"
            icon-only
            :icon="TrashIcon"
            as="button"
            type="button"
            size="lg"
            classes="ml-auto p-1! text-grey-dark hover:text-primary-dark shadow-none!"
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
      class="mb-4 flex items-center gap-3 rounded-md border border-primary-light/60 bg-white p-3"
    >
      <FormCheckbox
        v-model="includeAddOn"
        name="include_add_on"
        class="shrink-0 py-0!"
        label=""
        hide-label
        xl
      />

      <div class="flex flex-1 justify-between gap-3 text-base">
        <div class="flex flex-col">
          <h3
            class="font-semibold"
            v-text="item.add_on.title"
          />
          <p
            class="text-sm text-grey-dark"
            v-text="item.add_on.description"
          />
        </div>

        <p
          class="shrink-0"
          :class="
            includeAddOn ? 'text-xl font-semibold' : 'text-sm text-grey-dark'
          "
          v-text="`+${item.add_on.price}`"
        />
      </div>
    </div>
  </li>
</template>
