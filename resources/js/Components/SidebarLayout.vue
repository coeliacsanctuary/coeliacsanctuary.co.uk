<script lang="ts" setup>
withDefaults(
  defineProps<{ interleaved?: boolean; contentFirst?: boolean }>(),
  { interleaved: false, contentFirst: false },
);
</script>

<template>
  <div
    class="flex flex-col justify-between xmd:flex-row-reverse"
    :class="
      interleaved || contentFirst
        ? 'gap-4 xmd:gap-0'
        : 'space-y-4 xmd:space-y-0'
    "
  >
    <div
      class="xmd:flex-shrink-none w-full xmd:ml-4 xmd:w-1/3 xmd:max-w-xs lg:max-w-sm"
      :class="[
        interleaved
          ? 'contents xmd:flex xmd:flex-col xmd:gap-4'
          : 'flex flex-col space-y-4',
        contentFirst ? 'max-xmd:order-2' : '',
      ]"
    >
      <slot name="sidebar" />
    </div>

    <div
      :class="[
        interleaved
          ? 'contents xmd:flex xmd:flex-1 xmd:flex-col xmd:gap-4'
          : 'flex flex-1 flex-col',
        contentFirst ? 'max-xmd:order-1' : '',
      ]"
    >
      <slot />
    </div>
  </div>
</template>
