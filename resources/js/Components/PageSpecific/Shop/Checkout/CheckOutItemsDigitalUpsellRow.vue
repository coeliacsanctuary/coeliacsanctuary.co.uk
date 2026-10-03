<script setup lang="ts">
import { ShopBasketItemDigitalCardUpsell } from '@/types/Shop';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import eventBus from '@/eventBus';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import useAddToBasket from '@/composables/useAddToBasket';
import Loader from '@/Components/Loader.vue';

const props = defineProps<{ item: ShopBasketItemDigitalCardUpsell }>();

const inBasket = ref(props.item.isInBasket);

const { addBasketForm, prepareAddBasketDigitalCardForm, submitAddBasketForm } =
  useAddToBasket();

const addItem = () => {
  prepareAddBasketDigitalCardForm(props.item.id);
  submitAddBasketForm({
    only: ['basket', 'payment_intent'],
  });
};

// todo - wrong endpoint
const removeItem = () => {
  router.delete(`/shop/basket/${props.item.id}`, {
    preserveScroll: true,
    only: ['basket', 'has_basket', 'payment_intent'],
    onSuccess: () => {
      eventBus.$emit('refresh-payment-element');
    },
  });
};

watch(inBasket, (newValue) => {
  if (newValue) {
    addItem();
    return;
  }

  removeItem();
});
</script>

<template>
  <li class="relative">
    <Loader
      :display="addBasketForm.processing"
      absolute
      on-top
      blur
      color="secondary"
      size="size-12"
      width="border-8"
    />

    <div class="items center flex justify-between space-y-2 py-1">
      <FormCheckbox
        v-model="inBasket"
        :name="item.slug"
        :label="item.title"
        layout="left"
        class="p-0!"
        xl
      />

      <p
        class="shrink-0 text-base"
        :class="item.isInBasket ? 'font-semibold' : 'font-normal'"
        v-text="`${item.isInBasket ? '' : '+'}${item.item_price}`"
      />
    </div>
  </li>
</template>
