<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div>
        <button
          type="button"
          class="inline-flex cursor-pointer items-center rounded px-3 py-2 text-sm font-bold text-white shadow focus:outline-none"
          :class="isLoading ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-primary-dark'"
          :disabled="isLoading"
          @click="openPreview"
        >
          {{ isLoading ? 'Generating Preview...' : 'Preview' }}
        </button>

        <ul
          v-if="previewErrors.length"
          class="mt-2 space-y-1 text-sm text-red-500"
        >
          <li
            v-for="error in previewErrors"
            :key="error"
          >
            {{ error }}
          </li>
        </ul>
      </div>

      <Teleport to="body">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
          @click.self="closeModal"
        >
          <div
            class="relative flex flex-col rounded-lg shadow-2xl"
            style="width: 95vw; height: 95vh; background: white;"
          >
            <div class="flex items-center justify-between rounded-t-lg bg-gray-100 px-4 py-2">
              <span class="text-sm font-semibold text-gray-700">Blog Preview</span>
              <button
                type="button"
                class="text-gray-500 hover:text-gray-800 focus:outline-none"
                @click="closeModal"
              >
                ✕ Close
              </button>
            </div>

            <iframe
              :src="previewUrl"
              class="flex-1 rounded-b-lg border-0"
              style="width: 100%;"
            />
          </div>
        </div>
      </Teleport>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      isLoading: false,
      showModal: false,
      previewUrl: null,
      previewErrors: [],
    }
  },

  methods: {
    setInitialValue() {
      this.value = null
    },

    fill() {
      // No value to persist
    },

    getFieldValue(attribute) {
      const el = document.getElementById(attribute)
      if (!el) {
        return null
      }

      if (el.type === 'checkbox') {
        return el.checked
      }

      return el.value || null
    },

    async openPreview() {
      this.isLoading = true
      this.previewErrors = []

      const payload = {
        model: this.field.model,
        title: this.getFieldValue('title'),
        description: this.getFieldValue('description'),
        body: this.getFieldValue('body'),
        meta_tags: this.getFieldValue('meta_tags'),
        meta_description: this.getFieldValue('meta_description'),
        primary_image_url: this.field.primary_image_url ?? null,
        social_image_url: this.field.social_image_url ?? null,
        show_author: this.getFieldValue('show_author') ?? true,
      }

      try {
        const response = await Nova.request().post('/nova-vendor/preview-button/store', payload)
        this.previewUrl = response.data.url
        this.showModal = true
      } catch (error) {
        if (error.response?.status === 422) {
          const errors = error.response.data.errors
          this.previewErrors = Object.values(errors).flat()
        }
      } finally {
        this.isLoading = false
      }
    },

    closeModal() {
      this.showModal = false
      this.previewUrl = null
    },
  },
}
</script>
