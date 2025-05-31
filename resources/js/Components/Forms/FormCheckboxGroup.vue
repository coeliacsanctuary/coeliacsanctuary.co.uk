<script lang="ts" setup>
import { CheckboxItem } from '@/types/Types';

withDefaults(
  defineProps<{
    id?: string;
    label?: string;
  }>(),
  {
    id: `filter-checkbox-group-${Math.random() * (9999 - 1000) + 1000}`,
    label: undefined,
  },
);

const items = defineModel<CheckboxItem[]>();

const itemChecked = (index: number) => {
  if (items.value) {
    items.value[index].checked = !items.value[index].checked;
  }
};
</script>

<template>
  <fieldset>
    <legend
      v-if="label"
      class="mb-2 text-lg font-semibold"
      v-text="label"
    />

    <div class="divide-gray-light divide-y border-t border-b border-gray-200">
      <div
        v-for="(item, index) in items"
        :key="item.value"
        class="relative flex items-center py-1 xmd:py-2"
      >
        <div
          :class="{ 'cursor-not-allowed': item.disabled }"
          class="min-w-0 flex-1"
        >
          <label
            :class="{
              'text-grey': !item.disabled,
              'text-grey-off': item.disabled,
              'font-semibold': item.checked === true,
            }"
            :for="`${id}-${item.value}`"
            class="text-sm text-gray-900 select-none xmd:text-base"
            v-text="item.label"
          />
        </div>
        <div class="ml-3 flex items-center">
          <input
            :id="`${id}-${item.value}`"
            :checked="item.checked"
            :disabled="item.disabled"
            class="h-4 w-4 rounded-sm border-gray-300 text-primary focus:ring-primary xmd:h-5 xmd:w-5"
            type="checkbox"
            @change="itemChecked(index)"
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>
