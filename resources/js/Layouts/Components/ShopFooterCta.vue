<script setup lang="ts">
import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import EventBus from '@/eventBus';
import { computed, onMounted, ref } from 'vue';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { ShopBasketItem } from '@/types/Shop';
import { Page } from '@inertiajs/core';
import { usePage } from '@inertiajs/vue3';
import BasketSidebar from '@/Components/PageSpecific/Shop/BasketSidebar.vue';
import { ShoppingCartIcon, NoSymbolIcon } from '@heroicons/vue/24/outline';
import { pluralise } from '../../helpers';
import useGoogleEvents from '@/composables/useGoogleEvents';

const viewSideBar = ref(false);
const isVisible = ref(false);

const openSidebar = () => {
  viewSideBar.value = true;

  useGoogleEvents().googleEvent('event', 'checkout_progress', {
    event_category: 'opened-basket-sidebar',
  });
};

EventBus.$on('product-added-to-basket', openSidebar);
EventBus.$on('open-basket', openSidebar);
const page: Page<{ basket?: { items: ShopBasketItem[]; subtotal: string } }> =
  usePage();

const items = computed((): ShopBasketItem[] => page.props.basket?.items || []);
const subtotal = computed(() => page.props.basket?.subtotal || '');

const quantity = computed(() => {
  return items.value.reduce((total, item) => total + item.quantity, 0);
});

onMounted(() => {
  if (typeof document === 'undefined') {
    return;
  }

  new IntersectionObserver((entries) => {
    isVisible.value = entries[0].intersectionRatio === 0;
  }).observe(<Element>document.querySelector('#header-basket-detail'));
});
</script>

<template>
  <transition
    enter-active-class="duration-500 ease-out"
    enter-class="scale-50 translate-x-full opacity-0"
    enter-to-class="translate-x-0 scale-100 opacity-100"
    leave-active-class="duration-100 ease-in"
    leave-class="translate-x-0 scale-100 opacity-100"
    leave-to-class="translate-x-full scale-50 opacity-0"
  >
    <div
      v-if="isVisible"
      :class="[
        'safe-bottom',
        'translate',
        'group',
        'fixed',
        'bottom-0',
        'right-0',
        'mb-6',
        'mr-6',
        'transform',
        'cursor-pointer',
        'rounded-xl',
        'border',
        'border-white/20',
        'bg-primary-dark',
        'p-3',
        'text-white',
        'shadow-xl',
        'transition-all',
        'hover:scale-110',
        'xl:scale-75',
        '2xl:p-4',
      ]"
      @click="openSidebar()"
    >
      <div
        :class="[
          'absolute',
          'bottom-0',
          'right-full',
          'mr-[10px]',
          'mb-[2px]',
          'w-48',
          'rounded-lg',
          'border-2',
          'border-white',
          'bg-primary',
          'p-2',
          'text-sm',
          'text-center',
          'font-semibold',
          'uppercase',
          'leading-none',
          'opacity-0',
          'transition-all',
          'duration-300',
          'group-hover:opacity-100',
          'group-hover:delay-300',
          'md:ml-[8px]',
          'xmd:ml-[10px]',
          'xmd:mt-[-38px]',
          'xmd:text-base',
          'pointer-events-none',
        ]"
      >
        You have {{ quantity }} {{ pluralise('item', quantity) }}
        in your basket.
      </div>
      <div
        class="absolute top-0 right-0 -mt-3 -mr-3 flex h-6 w-6 items-center justify-center rounded-full bg-red"
        v-text="quantity"
      />
      <ShoppingBagIcon
        class="h-10 w-10 xl:max-2xl:h-12 xl:max-2xl:w-12 2xl:h-14 2xl:w-14"
      />
    </div>
  </transition>

  <Sidebar
    :open="viewSideBar"
    side="right"
    size="md"
    @close="viewSideBar = false"
  >
    <div class="flex flex-1 flex-col bg-white">
      <BasketSidebar
        v-if="items.length > 0"
        :items="items"
        :subtotal="subtotal"
        @close="viewSideBar = false"
      />

      <div
        v-else
        class="mt-8 p-3"
      >
        <div class="relative flex h-60 items-center justify-center opacity-50">
          <ShoppingCartIcon class="h-40 w-40 text-primary-light" />
          <NoSymbolIcon class="absolute h-60 w-60 text-primary-dark" />
        </div>

        <p
          class="text-center text-3xl leading-relaxed font-semibold opacity-50"
        >
          You have no items in your basket...
        </p>
      </div>
    </div>
  </Sidebar>
</template>
