<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { CoeliacButtonProps } from '@/types/Types';

type ConfirmationProps = {
  show: boolean;
  confirmButtonText?: string;
  cancelButtonText?: string;
  confirmTheme?: CoeliacButtonProps['theme'];
  cancelTheme?: CoeliacButtonProps['theme'];
  buttonSize?: CoeliacButtonProps['size'];
  size?: 'small' | 'medium' | 'relaxed' | 'large' | 'full';
};

withDefaults(defineProps<ConfirmationProps>(), {
  confirmButtonText: 'Confirm',
  cancelButtonText: 'Cancel',
  confirmTheme: 'negative',
  cancelTheme: 'primary',
  buttonSize: 'sm',
  size: 'small',
});

defineEmits(['cancel', 'confirm']);
</script>

<template>
  <Modal
    :open="show"
    :closeable="false"
    :size="size"
    overlay-classes="!z-[9999999999]"
  >
    <template #default>
      <slot />
    </template>

    <template #footer>
      <div class="flex items-center justify-center space-x-2">
        <CoeliacButton
          :theme="cancelTheme"
          as="button"
          :size="buttonSize"
          :label="cancelButtonText"
          @click="$emit('cancel')"
        />

        <CoeliacButton
          :theme="confirmTheme"
          as="button"
          :size="buttonSize"
          :label="confirmButtonText"
          @click="$emit('confirm')"
        />
      </div>
    </template>
  </Modal>
</template>
