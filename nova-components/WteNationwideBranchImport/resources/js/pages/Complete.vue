<template>
  <div>
    <Head title="Wte Nationwide Branch Import" />

    <Heading class="mb-6">Nationwide Branch Import</Heading>

    <Card class="flex flex-col p-4">
      Import Complete

      <ul>
        <li>Total Items Sent: {{ total }}</li>
        <li>Total Items Rejected (Import not valid): {{ rejected }}</li>
        <li>Total Items Processed: {{ processed }}</li>
        <li>Places Added: {{ added }}</li>
        <li>Failures: {{ failed }}</li>
      </ul>

      <template v-if="failed > 0">
        Failures:<br /><br />

        <ul class="flex flex-col space-y-2 text-xs">
          <li v-for="(message, name) in errors">
            <span class="font-semibold">{{ name }}</span> - {{ message }}
          </li>
        </ul>
      </template>
    </Card>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui';

export default {
  components: {
    Button,
  },

  props: {
    total: { required: true, type: Number },
    rejected: { required: true, type: Number },
    processed: { required: true, type: Number },
    added: { required: true, type: Number },
    failed: { required: true, type: Number },
    errors: { required: true, type: Array },
  },
};
</script>
