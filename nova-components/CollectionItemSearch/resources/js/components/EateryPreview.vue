<template>
  <div class="p-2">
    <p class="text-sm font-semibold">{{ item.title }}</p>
    <p v-if="item.address" class="text-xs text-gray-500 mt-0.5">{{ item.address }}</p>
    <p v-if="item.info" class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ item.info }}</p>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      required: true,
    },
    compact: {
      type: Boolean,
      default: false,
    },
  },
}
</script>
