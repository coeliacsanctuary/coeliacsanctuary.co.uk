<template>
  <div>
    <Head title="Wte Nationwide Branch Import" />

    <Heading class="mb-6">Nationwide Branch Import</Heading>

    <Card class="flex flex-col p-4">
      <table class="text-xs">
        <thead>
          <tr class="bg-primary-100 bg-opacity-10">
            <th class="border-primary-500 border-r border-l p-1">WTE ID</th>
            <th class="border-primary-500 border-r p-1">Name</th>
            <th class="border-primary-500 border-r p-1">Area</th>
            <th class="border-primary-500 border-r p-1">Town</th>
            <th class="border-primary-500 border-r p-1">County</th>
            <th class="border-primary-500 border-r p-1">Country</th>
            <th class="border-primary-500 border-r p-1">Address</th>
            <th class="border-primary-500 border-r p-1">Lat / Lng</th>
            <th class="border-primary-500 border-r p-1">Live</th>
          </tr>
        </thead>
        <tbody>
          <template
            v-for="(row, index) in items"
            :key="index"
          >
            <tr
              :class="{
                'bg-opacity-50 bg-red-500': row.error,
                'border-primary-500 border-b': !row.error,
              }"
            >
              <td
                class="border-primary-500 border-r border-l p-1 align-top"
                v-text="row.wheretoeat_id"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="row.name"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="
                  row.area.name ? `${row.area.name} - (${row.area.id})` : ''
                "
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="`${row.town.name} - (${row.town.id})`"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="`${row.county.name} - (${row.county.id})`"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="`${row.country.name} - (${row.country.id})`"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-html="row.address.formatted"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="`${row.lat}, ${row.lng}`"
              />
              <td
                class="border-primary-500 border-r p-1 align-top"
                v-text="row.live ? 'Yes' : 'No'"
              />
            </tr>
            <tr
              v-if="row.error"
              class="bg-opacity-50 border-primary-500 border-b bg-red-500"
            >
              <td
                colspan="9"
                v-html="row.message"
              />
            </tr>
          </template>
        </tbody>
      </table>
    </Card>

    <Card class="flex flex-col p-4">
      <form
        class="flex flex-col space-y-4"
        method="post"
        action="/cs-adm/wte-nationwide-branch-import/add"
        enctype="multipart/form-data"
      >
        <input
          type="hidden"
          name="_token"
          :value="csrf"
        />

        <input
          type="hidden"
          name="collection"
          :value="itemsJson"
        />

        <Button
          type="submit"
          class="bg-blue rounded px-8 py-4 text-lg"
        >
          Add Verified Places
        </Button>
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
    csrf: { required: true, type: String },
    items: { type: Array, required: true },
  },

  computed: {
    itemsJson() {
      return JSON.stringify(this.items);
    },
  },
};
</script>
