<script setup lang="ts">
import { computed, ref } from 'vue';
import { ShopProductDetail } from '@/types/Shop';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import useAddToBasket from '@/composables/useAddToBasket';
import useJourneyTracking from '@/composables/useJourneyTracking';
import eventBus from '@/eventBus';

const props = defineProps<{ product: ShopProductDetail; show: boolean }>();

const stickyNav = ref(false);

eventBus.$on('sticky-nav-on', () => (stickyNav.value = true));
eventBus.$on('sticky-nav-off', () => (stickyNav.value = false));

const primaryVariant = computed(() => props.product.variants[0]);

// Only a single-variant product can be added straight from the bar — anything with a choice to
// make has to send the customer back to the form.
const canAddDirectly = computed(() => props.product.variants.length === 1);

const inStock = computed(() =>
  props.product.variants.some((variant) => variant.quantity > 0),
);

const { addBasketForm, prepareAddBasketForm, submitAddBasketForm } =
  useAddToBasket();

if (props.product.variants.length === 1) {
  prepareAddBasketForm(props.product.id, primaryVariant.value.id);
}

const buttonLabel = computed((): string => {
  if (!inStock.value) {
    return 'Out of stock';
  }

  return canAddDirectly.value ? 'Add To Basket' : 'Choose an option';
});

const onClick = () => {
  useJourneyTracking().logEvent('clicked', 'ShopProduct/StickyBar', {
    title: props.product.title,
    action: canAddDirectly.value ? 'add-to-basket' : 'scroll-to-form',
  });

  if (!canAddDirectly.value) {
    document
      .getElementById('buy-box')
      ?.scrollIntoView({ behavior: 'smooth', block: 'center' });

    return;
  }

  submitAddBasketForm({ only: ['basket', 'errors'] });
};
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="-translate-y-full"
    enter-to-class="translate-y-0"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="translate-y-0"
    leave-to-class="-translate-y-full"
  >
    <div
      v-if="show"
      class="fixed inset-x-0 top-0 z-40 border-b border-primary-light bg-white/95 shadow-[0_2px_10px_rgba(0,0,0,0.08)] backdrop-blur-sm"
      :class="{ 'md:top-12': stickyNav }"
    >
      <div
        class="mx-auto flex w-full max-w-8xl items-center justify-between gap-4 p-3"
      >
        <div class="flex min-w-0 flex-col">
          <p
            class="truncate text-sm font-semibold max-sm:hidden"
            v-text="product.title"
          />

          <p
            class="text-xl leading-none font-semibold sm:text-2xl"
            v-text="product.prices.current_price"
          />
        </div>

        <CoeliacButton
          as="button"
          type="button"
          :label="buttonLabel"
          :theme="inStock ? 'secondary' : 'negative'"
          :disabled="!inStock"
          :icon="canAddDirectly && inStock ? ShoppingBagIcon : undefined"
          icon-position="right"
          size="xl"
          classes="shrink-0 whitespace-nowrap"
          bold
          :loading="addBasketForm.processing"
          @click="inStock ? onClick() : undefined"
        />
      </div>
    </div>
  </Transition>
</template>
