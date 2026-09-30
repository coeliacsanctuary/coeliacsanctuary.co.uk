<script setup lang="ts">
import { PurchasedCardEatSection } from '@/types/Shop';
import Icon from '@/Components/Icon.vue';

defineProps<{ details: PurchasedCardEatSection }>();

const iconStyle = (colour: string) => ({
  backgroundColor: colour,
  borderColor: `color-mix(in oklab, ${colour}, black 20%)`,
});
</script>

<template>
  <div class="flex items-center space-x-2">
    <div>
      <div
        class="flex size-8 items-center justify-center rounded-full border-2 text-white xs:size-10 lg:size-12"
        :style="iconStyle(details.colour.hex)"
      >
        <Icon
          :name="details.icon"
          class="xs:size-8 lg:size-10"
        />
      </div>
    </div>

    <div>
      <p class="prose-sm max-w-none xs:prose xs:prose-lg lg:prose-xl">
        <span
          class="font-semibold"
          :style="`color: ${details.colour.hex}`"
          v-text="details.label"
        />

        {{ details.items }}
      </p>
    </div>
  </div>
</template>
