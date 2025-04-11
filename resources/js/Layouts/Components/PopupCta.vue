<script setup lang="ts">
import { PopupProps } from '@/types/DefaultProps';
import Modal from '@/Components/Overlays/Modal.vue';
import { computed, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import useGoogleEvents from '@/composables/useGoogleEvents';
import useBrowser from '@/composables/useBrowser';
import useScreensize from '@/composables/useScreensize';

const props = defineProps<{ popup: PopupProps }>();

const displayModal = ref(false);

const handlePopupClick = () => {
  googleEvent('event', 'view_promotion', {
    event_label: 'clicked-global-shop-cta',
    promotions: [
      {
        id: 'shop-popup',
        name: 'global-shop-popup',
      },
    ],
  });

  router.get(props.popup.link);
};

const { googleEvent } = useGoogleEvents();

onMounted(() => {
  if ((useBrowser().pageWidth(1280) as number) <= 360) {
    return;
  }

  setTimeout(() => {
    displayModal.value = true;

    axios.post(`/popup/${props.popup.id}`).then(() => {
      //
    });

    googleEvent('event', 'view_promotion', {
      event_category: 'view-main-shop-popup',
      event_label: 'loaded-global-shop-cta',
    });
  }, 6000);
});

const imageUrl = computed<string>(() => {
  if (props.popup.secondary_image && useScreensize().isPortrait()) {
    return props.popup.secondary_image;
  }

  return props.popup.primary_image;
});
</script>

<template>
  <Modal
    :open="displayModal"
    no-padding
    size="large"
    :fit-screen="!!popup.secondary_image && useScreensize().isPortrait()"
    @close="displayModal = false"
  >
    <Link
      class="block"
      :href="popup.link"
      @click.prevent="handlePopupClick()"
    >
      <img
        :src="imageUrl"
        :alt="popup.text"
        :class="{
          'max-h-[90vh]':
            !!popup.secondary_image && useScreensize().isPortrait(),
        }"
      />
    </Link>

    <template #footer>
      <div
        class="prose prose-xl w-full max-w-none text-center lg:prose-2xl"
        v-text="popup.text"
      />
    </template>
  </Modal>
</template>
