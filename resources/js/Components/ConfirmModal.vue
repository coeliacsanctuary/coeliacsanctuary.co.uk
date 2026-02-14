<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

type ConfirmationProps = {
  show: boolean;
  confirmButtonText?: string;
  cancelButtonText?: string;
};

withDefaults(defineProps<ConfirmationProps>(), {
  confirmButtonText: 'Confirm',
  cancelButtonText: 'Cancel',
});

defineEmits(['cancel', 'confirm']);
</script>

<template>
  <Modal
    :open="show"
    :closable="false"
    size="small"
    overlay-classes="!z-[9999999999]"
  >
    <template #default>
      <slot />
    </template>

    <template #footer>
      <div class="flex items-center justify-center space-x-2">
        <CoeliacButton
          as="button"
          size="sm"
          :label="cancelButtonText"
          @click="$emit('cancel')"
        />

        <CoeliacButton
          theme="negative"
          as="button"
          size="sm"
          :label="confirmButtonText"
          @click="$emit('confirm')"
        />
      </div>
    </template>
  </Modal>
</template>
