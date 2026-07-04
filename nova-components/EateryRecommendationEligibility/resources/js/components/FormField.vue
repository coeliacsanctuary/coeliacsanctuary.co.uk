<template>
  <DefaultField :field="field" :errors="errors" :full-width-content="true">
    <template #field>
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center gap-3 mb-3">
          <span
            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
            :class="field.isEligible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
          >
            {{ field.isEligible ? '✓ Eligible for adding' : '✗ Not eligible' }}
          </span>
        </div>
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Explanation</h3>
        <div class="prose prose-sm text-gray-700 dark:text-gray-300 max-w-none" v-html="parsedExplanation" />
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  computed: {
    parsedExplanation() {
      return this.field.explanation
        ? DOMPurify.sanitize(marked.parse(this.field.explanation))
        : ''
    },
  },

  methods: {
    setInitialValue() {
      this.value = ''
    },

    fill(formData) {
      //
    },
  },
}
</script>
