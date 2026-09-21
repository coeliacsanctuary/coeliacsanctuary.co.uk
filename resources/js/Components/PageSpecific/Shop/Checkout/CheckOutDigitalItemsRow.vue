<script setup lang="ts">
import { ShopBasketDigitalItem } from '@/types/Shop';
import { TrashIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ref } from 'vue';
import eventBus from '@/eventBus';
import CountryFlag from '@/Components/CountryFlag.vue';

const props = withDefaults(
  defineProps<{
    item: ShopBasketDigitalItem;
    compact?: boolean;
    skipBundleRows?: boolean;
  }>(),
  { compact: false, skipBundleRows: true },
);

const isDeletingItem = ref(false);

// todo - wrong endpoint
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
</script>

<template>
  <li
    v-if="
      (item.hasBundleInBasket === false && skipBundleRows === true) ||
      skipBundleRows === false
    "
    class="relative"
  >
    <div
      class="flex"
      :class="compact ? 'gap-1 py-1' : 'gap-3 py-4 sm:gap-4'"
    >
      <div
        class="shrink-0 overflow-hidden rounded-lg"
        :class="compact ? 'h-[5.25] w-7' : 'h-15 w-20 sm:h-18 sm:w-24'"
      >
        <CountryFlag
          :country="item.country.label"
          :code="item.country.code"
          :width="compact ? '28' : '72'"
          :height="compact ? '21' : '54'"
          :class="compact ? 'mr-2!' : 'mx-2'"
        />
      </div>

      <div class="flex flex-1 flex-col gap-2">
        <div>
          <div class="flex justify-between gap-3 text-base">
            <h3
              class="font-semibold"
              v-text="item.title"
            />

            <div class="flex flex-col space-y-1">
              <p
                class="shrink-0 text-xl font-semibold"
                :class="compact ? 'text-base' : 'text-xl'"
                v-text="item.item_price"
              />

              <CoeliacButton
                theme="faded"
                icon-only
                :icon="TrashIcon"
                as="button"
                type="button"
                :size="compact ? 'md' : 'lg'"
                classes="ml-auto p-1! text-grey-dark hover:text-primary-dark shadow-none!"
                :loading="isDeletingItem"
                @click="removeItem()"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </li>
</template>
