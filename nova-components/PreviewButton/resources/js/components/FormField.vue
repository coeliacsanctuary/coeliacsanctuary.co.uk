<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div>
        <Button
          :loading="isLoading"
          @click="openPreview"
        >
          {{ isLoading ? 'Generating Preview...' : 'Preview' }}
        </Button>

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
            style="width: 95vw; height: 95vh; background: white"
          >
            <div
              class="flex items-center justify-between rounded-t-lg bg-gray-100 px-4 py-2"
            >
              <span class="text-sm font-semibold text-gray-700">
                Blog Preview
              </span>

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
              style="width: 100%"
            />
          </div>
        </div>
      </Teleport>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      isLoading: false,
      showModal: false,
      previewUrl: null,
      previewErrors: [],
    };
  },

  methods: {
    setInitialValue() {
      this.value = null;
    },

    fill() {
      // No value to persist
    },

    getGalleryFirstImageUrl(collection) {
      const input = document.getElementById(`__media__${collection}`);

      if (!input) {
        return null;
      }

      let el = input.parentElement;

      while (el && el !== document.body) {
        const img = el.querySelector('img.gallery-image');

        if (img?.src) {
          return img.src;
        }

        el = el.parentElement;
      }

      return null;
    },

    getBodyImages() {
      const input = document.getElementById('__media__body');

      if (!input) {
        return this.field.body_images ?? [];
      }

      let el = input.parentElement;

      while (el && el !== document.body) {
        const imgs = el.querySelectorAll('img.gallery-image');

        if (imgs.length > 0) {
          return Array.from(imgs).map((img) => ({
            file_name: img.alt,
            url: img.src || null,
          }));
        }

        el = el.parentElement;
      }

      return this.field.body_images ?? [];
    },

    getFieldValue(attribute) {
      const el =
        document.querySelector(`[dusk="${attribute}"]`) ||
        document.getElementById(attribute);

      if (!el) {
        return null;
      }

      if (el.type === 'checkbox') {
        return el.checked;
      }

      return el.value || null;
    },

    async openPreview() {
      this.isLoading = true;
      this.previewErrors = [];

      const payload = {
        model: this.field.model,
        title: this.getFieldValue('title'),
        description: this.getFieldValue('description'),
        body: this.getFieldValue('body'),
        primary_image_url:
          this.getGalleryFirstImageUrl('primary') ??
          this.field.primary_image_url ??
          null,
        social_image_url:
          this.getGalleryFirstImageUrl('social') ??
          this.field.social_image_url ??
          null,
        show_author: this.getFieldValue('show_author') ?? true,
        body_images: this.getBodyImages(),
      };

      try {
        const response = await Nova.request().post(
          '/nova-vendor/preview-button/store',
          payload,
        );

        if (!response.data?.url) {
          this.previewErrors = [
            'Preview could not be generated. Please try again.',
          ];

          return;
        }

        this.previewUrl = response.data.url;
        this.showModal = true;
      } catch (error) {
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          this.previewErrors = Object.values(errors).flat();
        } else {
          this.previewErrors = [
            'An unexpected error occurred. Please try again.',
          ];
        }
      } finally {
        this.isLoading = false;
      }
    },

    closeModal() {
      this.showModal = false;
      this.previewUrl = null;
    },
  },
};
</script>
