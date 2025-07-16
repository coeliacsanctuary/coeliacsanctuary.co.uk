<script setup lang="ts">
import { AnnouncementProps } from '@/types/DefaultProps';
import { ref } from 'vue';
import Modal from '@/Components/Overlays/Modal.vue';

defineProps<{ announcement: AnnouncementProps }>();

const showModal = ref(false);
</script>

<template>
  <div
    class="w-full cursor-pointer bg-red text-white"
    @click="showModal = true"
  >
    <div
      class="mx-auto flex w-full max-w-8xl items-center justify-between p-2 xmd:flex-col xmd:space-y-1"
    >
      <span
        class="truncate font-semibold"
        v-text="announcement.title"
      />
      <span class="text-sm italic">Read more</span>
    </div>
  </div>

  <Modal
    :open="showModal"
    @close="showModal = false"
  >
    <template #header>
      {{ announcement.title }}
    </template>

    <div
      class="prose max-w-none"
      v-html="announcement.text"
    />
  </Modal>
</template>
