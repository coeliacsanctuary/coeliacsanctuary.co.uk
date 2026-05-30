<template>
  <div class="flex items-start gap-3 p-2">
    <img
      v-if="item.imageUrl"
      :src="item.imageUrl"
      :alt="item.title"
      class="flex-shrink-0 rounded object-cover"
      style="width: 64px; height: 64px;"
    />
    <div v-else class="flex-shrink-0 rounded bg-gray-100" style="width: 64px; height: 64px;" />

    <div class="min-w-0">
      <p class="text-sm font-semibold truncate">{{ item.title }}</p>
      <p v-if="item.description" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ item.description }}</p>
    </div>
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
