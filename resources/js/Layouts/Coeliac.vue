<script lang="ts" setup>
import CoeliacHeader from '@/Layouts/Components/CoeliacHeader.vue';
import CoeliacFooter from '@/Layouts/Components/CoeliacFooter.vue';
import { AnnouncementProps, MetaProps, PopupProps } from '@/types/DefaultProps';
import { computed, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ShopBasketHeader from '@/Layouts/Components/ShopBasketHeader.vue';
import ShopFooterCta from '@/Layouts/Components/ShopFooterCta.vue';
import PopupCta from '@/Layouts/Components/PopupCta.vue';
import Loader from '@/Components/Loader.vue';
import eventBus from '@/eventBus';
import Announcement from '@/Layouts/Components/Announcement.vue';

defineProps<{
  meta: MetaProps;
  popup?: PopupProps;
  announcement?: AnnouncementProps;
}>();

const isShop = computed(
  (): boolean =>
    usePage().component.includes('Shop') &&
    usePage().component !== 'Shop/Checkout' &&
    usePage().component !== 'Shop/OrderComplete' &&
    usePage().component !== 'Shop/ReviewMyOrder',
);

const showLoader = ref(false);

eventBus.$on('show-site-loader', () => (showLoader.value = true));
eventBus.$on('hide-site-loader', () => (showLoader.value = false));

const isMounted = ref(false);

// onMounted(() => {
//   isMounted.value = true;
//
//   router.on('success', () => {
//     document
//       .querySelector('body')
//       ?.classList.toggle(
//         'no-auto-ads',
//         usePage().url.includes('/shop') ||
//           usePage().url.includes('/wheretoeat/browse'),
//       );
//   });
// });
</script>

<template>
  <Announcement
    v-if="announcement"
    :announcement="announcement"
  />

  <div class="relative flex min-h-screen flex-col bg-gray-100">
    <CoeliacHeader :metas="meta" />

    <div
      class="h-0 overflow-hidden"
      data-ad-break="off"
    />

    <ShopBasketHeader v-if="isShop" />

    <div
      class="h-0 overflow-hidden"
      data-ad-break="off"
    />

    <main class="mx-auto mb-3 flex w-full max-w-8xl flex-1 flex-col space-y-4">
      <slot />
    </main>

    <CoeliacFooter />

    <ShopFooterCta v-if="isShop" />

    <PopupCta
      v-if="popup"
      :popup="popup"
    />
  </div>

  <teleport
    v-if="isMounted"
    to="body"
  >
    <Loader
      :display="showLoader"
      size="size-24"
      width="border-10"
      background
      blur
      on-top
    />
  </teleport>
</template>
