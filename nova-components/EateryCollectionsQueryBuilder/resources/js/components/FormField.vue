<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <input
        :id="field.attribute"
        v-model="value"
        type="text"
        class="form-control form-control-bordered form-input w-full"
        :class="errorClasses"
        :placeholder="field.name"
      />

      <div class="w-full">
        <div class="flex w-full flex-col space-y-3">
          <div class="flex w-full items-center justify-between">
            <h3 class="text-lg font-bold">Where Clauses</h3>

            <Button
              as="span"
              @click="addWhereToPage()"
            >
              Add Where Clause
            </Button>
          </div>
          <div
            v-if="display.wheres.length"
            class="flex flex-col divide-y divide-gray-200"
          >
            <div
              v-for="(where, index) in display.wheres"
              :key="index"
              class="py-2"
            >
              <WhereClause @delete="() => deleteWhere(index)" />
            </div>
          </div>
          <span
            v-else
            class="my-16 italic"
          >
            No where clauses...
          </span>
          <div class="flex justify-end">
            <Button
              as="span"
              @click="addWhereToPage()"
            >
              Add Where Clause
            </Button>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { Button } from 'laravel-nova-ui';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import WhereClause from './components/WhereClause.vue';

export default {
  components: { WhereClause, Button },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    display: {
      wheres: [],
    },
    config: {
      averages: [],
      counts: [],
      joins: [],
      orders: [],
      wheres: [],
      limit: undefined,
    },
  }),

  methods: {
    addWhereToPage() {
      this.display.wheres.push({
        type: 'field',
        config: {},
      });
    },

    deleteWhere(index) {
      this.display.wheres.splice(index, 1);
    },

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

<style scoped>
.my-16 {
  margin-top: 4rem !important;
  margin-bottom: 4rem !important;
}
</style>
