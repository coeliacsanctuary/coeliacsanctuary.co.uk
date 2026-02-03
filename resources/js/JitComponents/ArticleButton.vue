<script setup lang="ts">
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { CoeliacButtonProps } from '@/types/Types';
import { onMounted, ref } from 'vue';

type Props = { wrapperStyles: string } & Omit<
  CoeliacButtonProps,
  | 'label'
  | 'as'
  | 'type'
  | 'icon'
  | 'iconPosition'
  | 'loading'
  | 'disabled'
  | 'iconOnly'
  | 'iconClasses'
>;

withDefaults(defineProps<Props>(), { wrapperStyles: '' });

const elem = ref<HTMLDivElement>();

const buttonLabel = ref('');

onMounted(() => {
  if (elem.value) {
    buttonLabel.value = elem.value.innerHTML;
  }
});
</script>

<template>
  <div
    :style="wrapperStyles"
    class="relative"
  >
    <CoeliacButton
      v-if="buttonLabel"
      :label="buttonLabel"
      as="a"
      v-bind="$props"
    />

    <div
      ref="elem"
      class="hidden"
    >
      <slot />
    </div>
  </div>
</template>
