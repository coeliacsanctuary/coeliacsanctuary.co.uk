<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <textarea
        :id="field.uniqueKey"
        v-model="value"
        :rows="field.rows"
        class="form-control form-input-bordered form-input h-auto w-full py-3"
        :class="errorClasses"
        :placeholder="field.name"
      />

      <p
        v-if="customError"
        class="my-2 text-red-500"
        v-text="customError"
      />
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import debounce from 'lodash/debounce';

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    customError: null,
  }),

  watch: {
    value: {
      handler: debounce(function (newVal) {
        this.validateHtml(newVal);
      }, 300),
      immediate: true, // Optional: validate on load
    },
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || '';
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.field.attribute, this.value || '');
    },

    validateHtml(content) {
      console.log('validating');

      const parser = new DOMParser();
      const doc = parser.parseFromString(`<div>${content}</div>`, 'text/html');
      const hasError = doc.querySelector('parsererror');

      console.log({ hasError, doc });

      if (hasError) {
        this.customError =
          'Invalid HTML detected. Please check for broken or mismatched tags.';

        return;
      }

      const mismatchedCaseTags =
        /<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>([^<>]*)<\/([a-zA-Z][a-zA-Z0-9]*)>/g;

      let match;
      while ((match = mismatchedCaseTags.exec(content)) !== null) {
        const [one, open, two, close] = match;

        if (open !== close) {
          this.customError = `Mismatched tag casing detected: <${open}>...</${close}>. Tag names must match exactly.`;
          return;
        }
      }

      this.customError = null;
    },
  },
};
</script>
