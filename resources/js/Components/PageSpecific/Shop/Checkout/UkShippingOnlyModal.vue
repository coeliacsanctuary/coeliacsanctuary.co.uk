<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const viewShippingModal = ref(false);
</script>

<template>
  <QuestionMarkCircleIcon
    class="size-4 cursor-pointer text-primary-dark transition hover:text-black"
    @click="viewShippingModal = true"
  />

  <Modal
    :open="viewShippingModal"
    size="relaxed"
    @close="viewShippingModal = false"
  >
    <template #header>
      <h3 class="pl-3 font-semibold lg:text-lg">International Shipping</h3>
    </template>

    <div class="flex flex-col space-y-2 p-3">
      <p class="prose max-w-none">
        Unfortunately, due to increased tax and customs charges, I'm no longer
        able to send my travel cards outside of the UK.
      </p>

      <p class="prose max-w-none">
        This means I'm only able to accept orders using a UK based payment card,
        and payments made using an international card will be declined.
      </p>

      <p class="prose max-w-none">
        I'm really sorry if this means you're no longer able to order from me,
        and for any disappointment or inconvenience this may cause.
      </p>
    </div>
  </Modal>
</template>
