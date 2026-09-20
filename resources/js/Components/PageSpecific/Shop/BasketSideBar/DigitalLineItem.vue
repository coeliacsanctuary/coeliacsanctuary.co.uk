<script setup lang="ts">
import { TrashIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import Loader from '@/Components/Loader.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShopBasketDigitalItem } from '@/types/Shop';
import { ref } from 'vue';
import useGoogleEvents from '@/composables/useGoogleEvents';
import useJourneyTracking from '@/composables/useJourneyTracking';
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

// todo - hook this up, this wont work as is as it wants a shop_items id, not shop_digital_items
const removeItem = () => {
  isDeletingItem.value = true;

  router.delete(`/shop/basket/${props.item.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      useGoogleEvents().googleEvent('event', 'remove-item-from-cart', {
        itemId: props.item.id,
      });

      useJourneyTracking().logEvent('clicked', 'ShopBasketSideBar/DeleteItem', {
        title: props.item.title,
      });
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
      class="relative flex"
      :class="compact ? 'py-1' : 'py-3'"
    >
      <div
        class="hidden shrink-0 overflow-hidden rounded-md sm:block"
        :class="compact ? 'h-[5.25] w-7' : 'h-18 w-24'"
      >
        <CountryFlag
          :country="item.country.label"
          :code="item.country.code"
          :width="compact ? '28' : '72'"
          :height="compact ? '21' : '54'"
        />
      </div>

      <div
        class="flex flex-1 flex-col"
        :class="compact ? 'sm:ml-2' : 'sm:ml-4'"
      >
        <div>
          <div
            class="flex justify-between font-medium text-gray-900"
            :class="compact ? 'text-sm' : 'text-base'"
          >
            <h3
              class="font-semibold"
              v-text="item.title"
            />

            <div class="flex flex-col space-y-1">
              <p
                class="ml-4"
                v-text="item.item_price"
              />

              <CoeliacButton
                theme="faded"
                icon-only
                :icon="TrashIcon"
                :size="compact ? 'lg' : 'xxl'"
                as="button"
                type="button"
                classes="p-1! hover:text-primary-dark !shadow-none"
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
