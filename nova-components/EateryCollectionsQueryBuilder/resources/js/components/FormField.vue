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

      {{ config }}

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
              <WhereClause
                :ref="(el) => (refs[index] = el)"
                @save="(clause) => saveWhere(clause)"
                @delete="() => deleteWhere(index)"
              />
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
import { average, count, join, where } from '../objects';

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
    refs: [],
  }),

  methods: {
    addWhereToPage() {
      this.display.wheres.push({
        type: 'field',
        config: {},
      });

      console.log('added to page', this.refs);
    },

    saveWhere(clause) {
      const clausesToAdd = this.prepareConfigItems(clause.type, clause.config);

      if (clausesToAdd.wheres.length) {
        this.config.wheres.push(...clausesToAdd.wheres);
      }

      if (clausesToAdd.joins.length) {
        this.config.joins.push(...clausesToAdd.joins);
      }

      if (clausesToAdd.counts.length) {
        this.config.counts.push(...clausesToAdd.counts);
      }

      if (clausesToAdd.averages.length) {
        this.config.averages.push(...clausesToAdd.averages.length);
      }
    },

    prepareConfigItems(type, config) {
      const wheres = [];
      const joins = [];
      const counts = [];
      const averages = [];

      switch (type) {
        case 'field':
        case 'relation':
          wheres.push(
            where(
              config.field,
              config.operator,
              config.value,
              config.boolean || 'and',
            ),
          );

          break;
        case 'has':
          wheres.push(where(config.field, config.operator, config.value));

          joins.push(
            join(config.table, config.localKey, '=', config.foreignKey),
          );

          break;
        case 'count':
          counts.push(
            count(
              config.table,
              config.localKey,
              config.foreignKey,
              config.alias,
              config.operator,
              config.value,
            ),
          );

          break;
        case 'average':
          averages.push(
            average(
              config.table,
              config.column,
              config.localKey,
              config.foreignKey,
              config.alias,
              config.operator,
              config.value,
            ),
          );

          break;
        case 'nested':
          const clauses = config.clauses.map((clause) =>
            this.prepareConfigItems(clause.type, {
              ...clause.config,
              boolean: clause.boolean,
            }),
          );

          if (clauses.wheres.length) {
            wheres.push(clauses.wheres);
          }

          if (clauses.joins.length) {
            joins.push(...clauses.joins);
          }

          if (clauses.counts.length) {
            counts.push(...clauses.counts);
          }

          if (clauses.averages.length) {
            averages.push(...clauses.averages.length);
          }

          break;
      }

      return { wheres, joins, counts, averages };
    },

    deleteWhere(index) {
      this.display.wheres.splice(index, 1);
      this.refs.splice(index, 1);

      this.$nextTick(() => {
        this.rebuildConfig();
      });
    },

    rebuildConfig() {
      this.config = {
        averages: [],
        counts: [],
        joins: [],
        orders: [],
        wheres: [],
        limit: undefined,
      };

      this.refs.forEach((ref) => ref.saveButton());
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
