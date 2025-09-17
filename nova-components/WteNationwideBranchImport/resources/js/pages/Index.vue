<template>
  <div>
    <Head title="Wte Nationwide Branch Import" />

    <Heading class="mb-6">Nationwide Branch Import</Heading>

    <Card class="flex flex-col">
      <form
        class="flex flex-col space-y-4 p-8"
        method="post"
        enctype="multipart/form-data"
        action="/cs-adm/wte-nationwide-branch-import/process"
      >
        <input
          type="hidden"
          name="_token"
          :value="csrf"
        />

        <div class="flex space-x-2">
          <label class="flex text-xl font-semibold">CSV</label>

          <input
            type="file"
            name="csv"
            accept="text/csv"
          />
        </div>

        <div
          v-if="errorMessage"
          class="font-semibold text-red-500"
          v-html="errorMessage"
        />

        <div>
          <Button
            type="submit"
            class="bg-blue w-auto rounded px-4 py-2"
          >
            Submit
          </Button>
        </div>
      </form>
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
    csrf: { type: String, required: true },
    errorMessage: { required: false },
  },
};
</script>
