<script setup lang="ts">
import {
  RadioGroup,
  RadioGroupDescription,
  RadioGroupLabel,
  RadioGroupOption,
} from '@headlessui/vue';
import Icon from '@/Components/Icon.vue';
import { ShopProductVariant } from '@/types/Shop';
import { computed } from 'vue';

const props = defineProps<{
  variants: ShopProductVariant[];
  variantLabel: string;
}>();

const model = defineModel<ShopProductVariant>();

const hasDescriptions = computed(() => {
  return props.variants.some((variant) => variant.description !== null);
});
</script>

<template>
  <div
    v-if="variants.length > 1"
    class="w-full sm:flex sm:justify-between"
  >
    <RadioGroup
      v-model="model"
      class="w-full"
    >
      <label
        class="block text-base leading-6 font-semibold text-primary-dark md:max-xl:text-lg xl:text-xl"
      >
        Select {{ variantLabel }}
        <span
          class="text-red"
          v-text="'*'"
        />
      </label>
      <div
        class="mt-1 grid w-full grid-cols-1 gap-3"
        :class="{ 'xxs:grid-cols-2': !hasDescriptions }"
      >
        <RadioGroupOption
          v-for="variant in variants"
          :key="variant.id"
          v-slot="{ checked, disabled }"
          as="template"
          :value="variant"
          :disabled="variant.quantity === 0"
        >
          <div
            :class="[
              checked
                ? 'bg-primary-light/50 font-semibold ring-2 ring-primary'
                : 'ring-0',
              disabled ? 'border-grey-off/30' : 'border-grey-off',
              'relative flex cursor-pointer items-center justify-between rounded-lg border p-3 outline-hidden',
            ]"
          >
            <div class="flex flex-1 flex-col">
              <RadioGroupLabel
                as="div"
                class="flex items-center space-x-2 text-base leading-none text-gray-900"
              >
                <Icon
                  v-if="variant.icon && variant.icon !== []"
                  :name="variant.icon.component"
                  :style="{ color: variant.icon.color }"
                />

                <div class="flex flex-1 justify-between">
                  <span
                    :class="{ 'text-grey-off': disabled }"
                    v-text="variant.title"
                  />
                  <span
                    v-if="disabled"
                    class="text-xs text-grey-dark italic"
                    v-text="'Out of stock'"
                  />
                </div>
              </RadioGroupLabel>

              <RadioGroupDescription
                v-if="variant.description"
                as="div"
                class="mt-1 text-sm text-gray-500"
              >
                {{ variant.description }}
              </RadioGroupDescription>
            </div>
          </div>
        </RadioGroupOption>
      </div>
    </RadioGroup>
  </div>
</template>
