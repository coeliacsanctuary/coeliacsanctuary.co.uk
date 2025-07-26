<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="relative">
        <input
          :id="field.attribute"
          v-model="value"
          type="text"
          class="form-control form-control-bordered form-input w-full"
          :class="errorClasses"
          placeholder="Search for a location...."
        />
        <div
          v-if="showResults && value !== ''"
          class="absolute z-50 w-full border border-gray-950/20 bg-white shadow-lg"
        >
          <ul>
            <li
              v-for="result in results"
              :key="`${result.type}-${result.matchedTerm}`"
              class="border-grey-300 cursor-pointer border-b p-2 transition last:border-b-0 hover:bg-gray-100"
              @click="select(result)"
            >
              <strong v-text="getType(result.type)" /> - {{ result.label }}
            </li>
          </ul>
        </div>
      </div>
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
    showResults: false,
    results: [],
    selectedResult: undefined,
  }),

  watch: {
    value(value) {
      this.search(value);
    },
  },

  methods: {
    getType(type) {
      switch (type) {
        case 'county':
          return 'County';
        case 'borough':
          return 'London Borough';
        case 'town':
          return 'Town';
        case 'area':
          return 'London Area';
      }
    },

    fill(formData) {
      this.fillIfVisible(
        formData,
        this.fieldAttribute,
        JSON.stringify(this.selectedResult) || '{}',
      );
    },

    select(result) {
      this.selectedResult = {
        countryId: result.countryId,
        countyId: result.countyId,
        townId: result.townId,
        areaId: result.areaId,
      };

      this.showResults = false;

      this.emitFieldValueChange(this.fieldAttribute, this.selectedResult);

      this.value = '';
    },

    search: debounce(function (term) {
      if (term === '') {
        this.showResults = false;
        this.results = [];
      }

      Nova.request()
        .post('/nova-vendor/eatery-location-search/search', { term })
        .then((response) => {
          this.results = response.data;
          this.showResults = true;
        });
    }, 100),
  },
};
</script>
