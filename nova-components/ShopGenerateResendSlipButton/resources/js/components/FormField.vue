<template>
  <DefaultField :field="currentField">
    <template #field>
      <Button
        :id="currentField.uniqueKey"
        size="large"
        @click.stop="generateSlip()"
      >
        Generate Dispatch Slip
      </Button>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova';
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },
  mixins: [DependentFormField, HandlesValidationErrors],

  props: ['resourceId'],

  methods: {
    generateSlip() {
      window.open(
        `/cs-adm/order-dispatch-slip/${this.currentField.orderId}?resend=true&options=${JSON.stringify(this.currentField.options)}`,
        '_blank',
      );
    },
  },
};
</script>
