<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
    style="display: none !important"
  >
    <template #field>
      <input
        :id="field.attribute"
        v-model="value"
        type="hidden"
      />
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  mounted() {
    const searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has('place_recommendation_id')) {
      // this.value = searchParams.get('place_recommendation_id');
    }
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
      formData.append(this.fieldAttribute, this.value || '');
    },
  },
};
</script>
