<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText" :full-width-content="fullWidthContent">
    <template #field>
      <span v-if="field.status === 'none'" class="text-gray-400">—</span>
      <Loader v-else-if="field.status === 'pending'" :width="20" />
      <IconBoolean v-else-if="field.status === 'completed'" :value="true" />
      <IconBoolean v-else-if="field.status === 'failed'" :value="false" />
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

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
