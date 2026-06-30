<template>
  <Modal :show="show" size="3xl" @close="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
      <ModalHeader>AI Recommendation Data</ModalHeader>

      <div class="px-8 py-6">
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-4">
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
              :class="data.is_eligible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
              {{ data.is_eligible ? 'Eligible for adding' : 'Not eligible' }}
            </span>
          </div>

          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Explanation</h3>
          <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ data.explanation }}</p>
        </div>

        <hr class="mb-6 border-gray-100 dark:border-gray-700" />

        <dl class="flex flex-col gap-y-2">
          <div
            v-for="field in dataFields"
            :key="field.label"
            class="flex items-baseline gap-2"
          >
            <dt class="w-32 shrink-0 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ field.label }}</dt>
            <dd class="flex-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
              <template v-if="field.value === null || field.value === undefined">—</template>
              <template v-else-if="Array.isArray(field.value)">
                {{ field.value.length ? field.value.join(', ') : '—' }}
              </template>
              <template v-else>{{ field.value }}</template>
            </dd>
          </div>
        </dl>
      </div>

      <ModalFooter>
        <div class="ml-auto">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600"
            @click="$emit('close')"
          >
            Close
          </button>
        </div>
      </ModalFooter>
    </div>
  </Modal>
</template>

<script>
export default {
  props: {
    show: { type: Boolean, default: false },
    data: { type: Object, required: true },
  },
  emits: ['close'],
  computed: {
    dataFields() {
      return [
        { label: 'Name', value: this.data.place_name },
        { label: 'Address', value: this.data.place_address },
        { label: 'Country', value: this.data.place_country },
        { label: 'County', value: this.data.place_county },
        { label: 'Town', value: this.data.place_town },
        { label: 'Area', value: this.data.place_area },
        { label: 'Latitude', value: this.data.latitude },
        { label: 'Longitude', value: this.data.longitude },
        { label: 'Phone', value: this.data.phone_number },
        { label: 'Website', value: this.data.website },
        { label: 'Facebook', value: this.data.facebook },
        { label: 'Instagram', value: this.data.instagram },
        { label: 'Eatery Type', value: this.data.eatery_type },
        { label: 'Venue Type', value: this.data.venue_type },
        { label: 'Cuisine', value: this.data.cuisine },
        { label: 'Features', value: this.data.features },
        { label: 'Info', value: this.data.info },
      ]
    },
  },
}
</script>
