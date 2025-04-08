<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import {
  InformationCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { CoeliacButtonProps } from '@/types/Types';

type Action = {
  theme: CoeliacButtonProps['theme'];
  size: CoeliacButtonProps['size'];
  label: string;
  action: () => void;
};

const props = withDefaults(
  defineProps<{
    open?: boolean;
    theme?: 'info' | 'warning';
    title?: string;
    actions?: Action[];
  }>(),
  {
    open: true,
    theme: 'info',
    title: undefined,
    actions: undefined,
  },
);

const titleLabel = computed<string>(() => {
  if (props.title) {
    return props.title;
  }

  switch (props.theme) {
    case 'info':
    default:
      return 'Notice';
    case 'warning':
      return 'Warning';
  }
});
</script>

<template>
  <Modal
    :open="open"
    :closeable="false"
    size="relaxed"
    width="w-full"
  >
    <div class="flex space-x-8 p-3">
      <div
        :class="{
          'text-primary-dark border-primary-dark/50': theme === 'info',
          'text-red-dark border-red-dark/50': theme === 'warning',
        }"
        class="pr-6 border-r"
      >
        <InformationCircleIcon
          v-if="theme === 'info'"
          class="size-20"
        />

        <ExclamationCircleIcon
          v-if="theme === 'warning'"
          class="size-20"
        />
      </div>

      <div class="flex-1 flex flex-col space-y-3">
        <p
          class="text-xl font-semibold"
          :class="{
            'text-primary-dark': theme === 'info',
            'text-red-dark': theme === 'warning',
          }"
          v-text="titleLabel"
        />

        <div>
          <slot />
        </div>

        <div
          v-if="actions.length"
          class="flex space-x-2 justify-end"
        >
          <CoeliacButton
            v-for="(action, index) in actions"
            :key="index"
            :theme="action.theme"
            :label="action.label"
            :size="action.size"
            @click="action.action()"
          />
        </div>
      </div>
    </div>
  </Modal>
</template>
