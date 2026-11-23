<template>
  <div
    class="cb-tile bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded"
  >
    <div class="cb-tile-media bg-gray-100 dark:bg-gray-900">
      <img
        v-if="imageUrl"
        :src="imageUrl"
        :alt="title"
        loading="lazy"
        class="cb-tile-image"
      />

      <div v-else class="cb-tile-placeholder text-gray-400">
        <Icon :name="placeholderIcon" type="outline" />
      </div>
    </div>

    <div class="cb-tile-body">
      <p class="cb-tile-title text-sm font-semibold text-gray-800 dark:text-gray-200">
        {{ title }}
      </p>

      <p class="cb-tile-meta text-xs text-gray-500">{{ meta }}</p>
    </div>

    <div class="cb-tile-footer border-t border-gray-100 dark:border-gray-700">
      <button
        type="button"
        class="cb-tile-action text-xs text-primary-500 hover:text-primary-400"
        @click="$emit('edit')"
      >
        Edit
      </button>

      <button
        type="button"
        class="cb-tile-action text-xs text-red-500 hover:text-red-700"
        @click="$emit('remove')"
      >
        Remove
      </button>
    </div>
  </div>
</template>

<script>
import { Icon } from 'laravel-nova-ui'

const PLACEHOLDER_ICONS = {
  'App\\Models\\Blogs\\Blog': 'book-open',
  'App\\Models\\Recipes\\Recipe': 'cake',
  'App\\Models\\EatingOut\\Eatery': 'building-storefront',
  'App\\Models\\EatingOut\\NationwideBranch': 'building-storefront',
}

export default {
  components: { Icon },

  props: {
    row: {
      type: Object,
      required: true,
    },
  },

  emits: ['edit', 'remove'],

  computed: {
    imageUrl() {
      return this.row.preview?.imageUrl ?? null
    },

    title() {
      return this.row.item_title || this.row.preview?.title || 'No item selected'
    },

    meta() {
      return (
        this.row.item_description ||
        this.row.preview?.address ||
        this.row.preview?.description ||
        ''
      )
    },

    placeholderIcon() {
      return PLACEHOLDER_ICONS[this.row.item_type] ?? 'photo'
    },
  },
}
</script>
