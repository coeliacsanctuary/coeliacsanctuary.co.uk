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

      <div class="flex w-full flex-col">
        <div
          class="flex w-full flex-col space-y-3 border-b border-gray-200 pb-3"
        >
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
                :ref="(el) => (whereRefs[index] = el)"
                @save="(clause) => saveWhere(clause)"
                @edit="() => rebuildConfig()"
                @delete="() => deleteWhere(index)"
              />
            </div>
          </div>
          <span
            v-else
            class="my-8 italic"
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

        <div
          class="flex w-full flex-col space-y-3 border-b border-gray-200 py-3"
        >
          <div class="flex w-full items-center justify-between">
            <h3 class="text-lg font-bold">Order Clauses</h3>

            <Button
              as="span"
              @click="addOrderToPage()"
            >
              Add Order Clause
            </Button>
          </div>
          <div
            v-if="display.orders.length"
            class="flex flex-col divide-y divide-gray-200"
          >
            <div
              v-for="(order, index) in display.orders"
              :key="index"
              class="py-2"
            >
              <OrderClause
                :ref="(el) => (orderRefs[index] = el)"
                @save="(clause) => saveOrder(clause)"
                @edit="() => rebuildConfig()"
                @delete="() => deleteOrder(index)"
              />
            </div>
          </div>
          <span
            v-else
            class="my-8 italic"
          >
            No order clauses...
          </span>
          <div class="flex justify-end">
            <Button
              as="span"
              @click="addOrderToPage()"
            >
              Add Order Clause
            </Button>
          </div>
        </div>

        <div class="mt-3 flex w-full flex-col space-y-3">
          <div class="flex w-full items-center justify-between">
            <h3 class="text-lg font-bold">Limit Results</h3>
          </div>

          <div class="flex flex-col py-2">
            <input
              class="form-control form-control-bordered px-2"
              v-model.number="config.limit"
            />
            <span class="text-xs">Leave blank for no limit</span>
          </div>
        </div>
      </div>

      <div class="flex w-full flex-col space-y-3 border-t border-gray-200 pt-3">
        <div class="flex justify-between">
          <h3 class="text-lg font-semibold">Preview Query</h3>

          <Button
            as="span"
            @click="previewQuery()"
          >
            Generate Query
          </Button>
        </div>

        <div v-if="queries.ran">
          <Loader v-if="queries.loading" />

          <div
            class="grid grid-cols-6 gap-2"
            v-if="!queries.loading"
          >
            <span>Eateries</span>
            <div class="col-span-5">
              <pre
                style="white-space: normal"
                v-text="queries.eateries"
              />
            </div>

            <span>Branches</span>
            <div class="col-span-5">
              <pre
                style="white-space: normal"
                v-text="queries.branches"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="flex w-full flex-col space-y-3 border-t border-gray-200 pt-3">
        <div class="flex justify-between">
          <h3 class="text-lg font-semibold">Preview Results</h3>

          <Button
            as="span"
            @click="executeQueries()"
          >
            Execute Query
          </Button>
        </div>

        <div v-if="results.ran">
          <Loader v-if="results.loading" />

          <div
            class=""
            v-if="!results.loading"
          >
            <ul class="flex flex-col space-y-3">
              <li
                v-for="eatery in results.data.data"
                class="rounded border p-2 shadow"
              >
                <div class="flex justify-between">
                  <div class="flex-1">
                    <h2
                      v-text="
                        eatery.branch && eatery.branch.name
                          ? eatery.branch.name
                          : eatery.name
                      "
                    />
                    <span
                      v-text="
                        eatery.branch && eatery.branch.location?.address
                          ? eatery.branch.location.address
                          : eatery.location.address
                      "
                    />
                  </div>

                  <div class="flex flex-col space-y-1">
                    <Button
                      size="small"
                      as="a"
                      :href="
                        eatery.branch
                          ? `/cs-adm/resources/nationwide-branches/${eatery.branch.id}/edit?viaRelationship=nationwideBranches&viaResource=nationwide-eateries&viaResourceId=${eatery.id}`
                          : `/cs-adm/resources/eateries/${eatery.id}/edit`
                      "
                      target="_blank"
                    >
                      Edit
                    </Button>
                    <Button
                      size="small"
                      as="a"
                      :href="eatery.link"
                      target="_blank"
                    >
                      View Eatery
                    </Button>
                  </div>
                </div>
              </li>
            </ul>

            <span>
              Showing {{ results.data.to }} of {{ results.data.total }} results
              over {{ results.data.last_page }} pages.
            </span>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { Button, Loader } from 'laravel-nova-ui';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import WhereClause from './components/WhereClause.vue';
import { average, count, join, order, where } from '../objects';
import OrderClause from './components/OrderClause.vue';

export default {
  components: { OrderClause, WhereClause, Button, Loader },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    display: {
      wheres: [],
      orders: [],
    },
    config: {
      averages: [],
      counts: [],
      joins: [],
      orders: [],
      wheres: [],
      limit: undefined,
    },
    whereRefs: [],
    orderRefs: [],
    queries: {
      ran: false,
      loading: false,
      eateries: undefined,
      branches: undefined,
    },
    results: {
      ran: false,
      loading: false,
      data: [],
    },
  }),

  methods: {
    resetPreviewQuery() {
      this.queries.ran = false;
      this.queries.loading = false;
      this.queries.eateries = undefined;
      this.queries.branches = undefined;
    },

    resetResults() {
      this.results.ran = false;
      this.results.loading = false;
      this.results.data = [];
    },

    addWhereToPage() {
      this.resetPreviewQuery();

      this.display.wheres.push({
        type: 'field',
        config: {},
      });
    },

    addOrderToPage() {
      this.resetPreviewQuery();

      this.display.orders.push({});
    },

    saveWhere(clause) {
      this.resetPreviewQuery();

      const clausesToAdd = this.processWhereClause(clause.type, clause.config);

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
        this.config.averages.push(...clausesToAdd.averages);
      }
    },

    deleteWhere(index) {
      this.resetPreviewQuery();

      this.display.wheres.splice(index, 1);
      this.whereRefs.splice(index, 1);

      this.$nextTick(() => {
        this.rebuildConfig();
      });
    },

    saveOrder(clause) {
      this.resetPreviewQuery();

      this.config.orders.push(
        order(
          clause.config.column,
          clause.config.direction,
          clause.config.table,
          clause.config.localKey,
          clause.config.foreignKey,
        ),
      );

      if (clause.config.additional?.wheres?.length) {
        this.config.wheres.push(...clause.config.additional.wheres);
      }

      if (clause.config.additional?.joins?.length) {
        this.config.joins.push(...clause.config.additional.joins);
      }

      if (clause.config.additional?.counts?.length) {
        this.config.counts.push(...clause.config.additional.counts);
      }

      if (clause.config.additional?.averages?.length) {
        this.config.averages.push(...clause.config.additional.averages.length);
      }
    },

    deleteOrder(index) {
      this.resetPreviewQuery();

      this.display.orders.splice(index, 1);
      this.orderRefs.splice(index, 1);

      this.$nextTick(() => {
        this.rebuildConfig();
      });
    },

    processWhereClause(type, config) {
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
          const clauses = config.clauses
            .map((clause) =>
              this.processWhereClause(clause.config.type, {
                ...clause.config.config,
                boolean: clause.boolean,
              }),
            )
            .reduce(
              (acc, cur) => {
                Object.keys(cur).forEach((key) => acc[key].push(...cur[key]));
                return acc;
              },
              { wheres: [], joins: [], counts: [], averages: [] },
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

    rebuildConfig() {
      this.resetPreviewQuery();

      this.config = {
        averages: [],
        counts: [],
        joins: [],
        orders: [],
        wheres: [],
        limit: undefined,
      };

      this.whereRefs.filter((t) => t).forEach((ref) => ref.forceSave());
      this.orderRefs.filter((t) => t).forEach((ref) => ref.forceSave());
    },

    generateProcessedConfig() {
      return {
        wheres: this.processWheres(this.config.wheres),
        joins: this.config.joins.map((join) => Object.values(join)),
        counts: this.config.counts.map((count) => Object.values(count)),
        averages: this.config.averages.map((avg) => Object.values(avg)),
        orderBy: this.config.orders.map((order) => Object.values(order)),
        limit: this.config.limit,
      };
    },

    processWheres(whereClauses) {
      return whereClauses.map((where) => {
        if (Array.isArray(where)) {
          return this.processWheres(where);
        }

        return Object.values(where);
      });
    },

    previewQuery() {
      this.rebuildConfig();
      this.resetPreviewQuery();

      this.queries.ran = true;
      this.queries.loading = true;

      Nova.request()
        .post('/nova-vendor/eatery-collections-query-builder/preview-query', {
          config: this.generateProcessedConfig(),
        })
        .then((response) => {
          this.queries.eateries = response.data.eateries;
          this.queries.branches = response.data.branches;

          this.queries.loading = false;
        });
    },

    executeQueries() {
      this.rebuildConfig();
      this.resetResults();

      this.results.ran = true;
      this.results.loading = true;

      Nova.request()
        .post('/nova-vendor/eatery-collections-query-builder/results', {
          config: this.generateProcessedConfig(),
        })
        .then((response) => {
          this.results.data = response.data.data;

          this.results.loading = false;
        });
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
      this.rebuildConfig();

      console.log(JSON.stringify(this.generateProcessedConfig()));
      formData.append(
        this.fieldAttribute,
        JSON.stringify(this.generateProcessedConfig()),
      );
    },
  },
};
</script>

<style scoped>
.my-8 {
  margin-top: 2rem !important;
  margin-bottom: 2rem !important;
}

.col-span-5 {
  grid-column: span 5 / span 5;
}
</style>
