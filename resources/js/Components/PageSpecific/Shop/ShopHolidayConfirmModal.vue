<script setup lang="ts">
import { ref } from 'vue';
import { CalendarDaysIcon } from '@heroicons/vue/24/outline';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import eventBus from '@/eventBus';
import useLocalStorage from '@/composables/useLocalStorage';
import { ShopHolidayConfirmation } from '@/composables/useAddToBasket';

const { putInLocalStorage } = useLocalStorage();

const confirmation = ref<ShopHolidayConfirmation | null>(null);

eventBus.$on<ShopHolidayConfirmation>(
  'confirm-shop-holiday',
  (payload) => (confirmation.value = payload),
);

const confirm = () => {
  if (!confirmation.value) {
    return;
  }

  const { storageKey, onConfirm } = confirmation.value;

  putInLocalStorage(storageKey, true);
  confirmation.value = null;

  onConfirm();
};
</script>

<template>
  <ConfirmModal
    :show="confirmation !== null"
    size="medium"
    button-size="lg"
    confirm-button-text="Yes, add to my basket"
    cancel-button-text="Cancel"
    confirm-theme="primary"
    cancel-theme="ghost"
    @cancel="confirmation = null"
    @confirm="confirm()"
  >
    <div class="flex flex-col items-center gap-4 p-4 text-center sm:p-6">
      <div class="rounded-full bg-primary-light/50 p-3">
        <CalendarDaysIcon class="size-8 text-primary-dark" />
      </div>

      <h2 class="text-xl font-semibold sm:text-2xl">Just so you know...</h2>

      <p
        class="text-base leading-relaxed text-grey-dark"
        v-text="confirmation?.notice"
      />

      <p class="text-base font-semibold">Do you want to continue?</p>
    </div>
  </ConfirmModal>
</template>
