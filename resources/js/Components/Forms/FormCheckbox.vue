<script lang="ts" setup>
import { CheckboxProps, CheckboxPropsDefault } from '@/Components/Forms/Props';

const props: CheckboxProps = withDefaults(
  defineProps<CheckboxProps>(),
  CheckboxPropsDefault,
);

const value = defineModel<boolean>();
</script>

<template>
  <div
    class="relative flex items-center py-1 xmd:py-2"
    :class="{ 'flex-row-reverse': layout === 'left' }"
  >
    <div
      :class="{ 'cursor-not-allowed': disabled }"
      class="min-w-0 flex-1"
    >
      <label
        :class="{
          'text-grey': !disabled,
          'text-grey-off': disabled,
          'font-semibold': value === true,
          'font-semibold text-primary-dark': highlight,
        }"
        :for="name"
        class="text-sm select-none xmd:text-base"
        v-text="label"
      />
    </div>

    <div
      class="flex items-center"
      :class="layout === 'right' ? 'ml-3' : 'mr-3'"
    >
      <input
        :id="name"
        v-model="value"
        :disabled="disabled"
        class="rounded-sm border-gray-300 text-primary focus:ring-primary"
        :class="props.xl ? 'h-6 w-6' : 'h-4 w-4 xmd:h-5 xmd:w-5'"
        type="checkbox"
      />
    </div>
  </div>
</template>
