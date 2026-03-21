<script setup>
import { ref, watch } from 'vue';
import {
  whereAverage,
  whereCount,
  whereFields,
  whereHas,
  whereRelations,
} from '../../data';
import { Button } from 'laravel-nova-ui';

defineEmits(['save', 'delete']);

const type = ref();
const config = ref({});
const relationValues = ref([]);

const operators = ['=', '!=', '<', '>', '<=', '>='];

const saveButton = () => {
  //validate
  //save
};

watch(
  () => type.value,
  () => {
    switch (type.value) {
      case 'field':
        config.value = {
          field: '',
          operator: '=',
          value: '',
        };

        break;
      case 'relation':
        config.value = {
          field: '',
          operator: '=',
          value: '',
        };

        relationValues.value = [];

        break;
      case 'has':
        config.value = {
          relation: '',
          field: '',
          operator: '=',
          value: '',
        };

        relationValues.value = [];

        break;
      case 'count':
        config.value = {
          relation: '',
          localKey: '',
          foreignKey: '',
          alias: '',
          operator: '=',
          value: '',
        };

        break;
      case 'average':
        config.value = {
          relation: '',
          column: '',
          localKey: '',
          foreignKey: '',
          alias: '',
          operator: '=',
          value: '',
        };

        break;

      case 'nested':
        config.value = {
          clauses: [],
        };
    }
  },
);

watch(
  () => config.value?.field,
  () => {
    if (config.value.field === '') {
      return;
    }

    if (type.value === 'relation') {
      Nova.request()
        .post('/nova-vendor/eatery-collections-query-builder/relation', {
          relation: config.value.field,
        })
        .then((response) => {
          relationValues.value = response.data;
        });
    }

    if (type.value === 'has') {
      Nova.request()
        .post('/nova-vendor/eatery-collections-query-builder/has', {
          relation: config.value.relation,
        })
        .then((response) => {
          relationValues.value = response.data;
        });
    }
  },
);

watch(
  () => config.value?.relation,
  () => {
    if (config.value.relation === '') {
      return;
    }

    if (type.value === 'has') {
      const raw = whereHas.find(
        (has) => has.relation === config.value.relation,
      );

      config.value.field = raw.column;
    }

    if (type.value === 'count') {
      const raw = whereCount.find(
        (count) => count.label === config.value.relation,
      );

      config.value.localKey = raw.localKey;
      config.value.foreignKey = raw.foreignKey;
      config.value.alias = raw.alias;
    }

    if (type.value === 'average') {
      const raw = whereAverage.find(
        (avg) => avg.label === config.value.relation,
      );

      config.value.column = raw.column;
      config.value.localKey = raw.localKey;
      config.value.foreignKey = raw.foreignKey;
      config.value.alias = raw.alias;
    }
  },
);
</script>

<template>
  <div class="grid grid-cols-6 gap-2">
    <div>
      <select
        v-model="type"
        class="form-control form-control-bordered"
      >
        <option
          disabled
          selected
        >
          Select a type
        </option>
        <option value="field">Field</option>
        <option value="relation">Relation</option>
        <option value="has">Has</option>
        <option value="count">Count</option>
        <option value="average">Average</option>
        <option value="nested">Nested</option>
      </select>
    </div>

    <template v-if="type === 'field'">
      <select
        v-model="config.field"
        class="form-control form-control-bordered"
      >
        <option disabled>Column</option>
        <option
          v-for="field in whereFields"
          :key="field"
          v-text="field"
        />
      </select>
      <select
        v-model="config.operator"
        class="form-control form-control-bordered"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          v-text="operator"
        />
      </select>
      <input
        v-model="config.value"
        placeholder="Value"
        type="text"
        class="form-control form-control-bordered"
      />
      <div />
      <div class="text-right">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button @click="saveButton()"> Save </Button>
      </div>
    </template>

    <template v-if="type === 'relation'">
      <select
        v-model="config.field"
        class="form-control form-control-bordered"
      >
        <option disabled>Relation</option>
        <option
          v-for="relation in whereRelations"
          :key="relation"
          :value="relation.column"
          v-text="relation.label"
        />
      </select>
      <select
        v-model="config.operator"
        class="form-control form-control-bordered"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          v-text="operator"
        />
      </select>
      <select
        v-model="config.value"
        class="form-control form-control-bordered"
      >
        <option
          v-for="option in relationValues"
          :key="option.value"
          :value="option.value"
          v-text="option.label"
        />
      </select>
      <div />
      <div class="text-right">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button @click="saveButton()"> Save </Button>
      </div>
    </template>

    <template v-if="type === 'has'">
      <select
        v-model="config.relation"
        class="form-control form-control-bordered"
      >
        <option disabled>Relation</option>
        <option
          v-for="relation in whereHas"
          :key="relation.relation"
          :value="relation.relation"
          v-text="relation.label"
        />
      </select>
      <input
        v-model="config.field"
        placeholder="Field"
        type="text"
        disabled
        class="form-control form-control-bordered"
      />
      <select
        v-model="config.operator"
        class="form-control form-control-bordered"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          v-text="operator"
        />
      </select>
      <select
        v-model="config.value"
        class="form-control form-control-bordered"
      >
        <option
          v-for="option in relationValues"
          :key="option.value"
          :value="option.value"
          v-text="option.label"
        />
      </select>
      <div class="text-right">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button @click="saveButton()"> Save </Button>
      </div>
    </template>

    <template v-if="type === 'count'">
      <select
        v-model="config.relation"
        class="form-control form-control-bordered"
      >
        <option disabled>Relation</option>
        <option
          v-for="relation in whereCount"
          :key="relation.label"
          :value="relation.label"
          v-text="relation.label"
        />
      </select>
      <select
        v-model="config.operator"
        class="form-control form-control-bordered"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          v-text="operator"
        />
      </select>
      <input
        v-model="config.value"
        placeholder="Value"
        type="number"
        class="form-control form-control-bordered"
      />
      <div />
      <div class="text-right">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button @click="saveButton()"> Save </Button>
      </div>
    </template>

    <template v-if="type === 'average'">
      <select
        v-model="config.relation"
        class="form-control form-control-bordered"
      >
        <option disabled>Relation</option>
        <option
          v-for="relation in whereAverage"
          :key="relation.label"
          :value="relation.label"
          v-text="relation.label"
        />
      </select>
      <input
        v-model="config.column"
        placeholder="Column"
        type="text"
        disabled
        class="form-control form-control-bordered"
      />
      <select
        v-model="config.operator"
        class="form-control form-control-bordered"
      >
        <option
          v-for="operator in operators"
          :key="operator"
          v-text="operator"
        />
      </select>
      <input
        v-model="config.value"
        placeholder="Value"
        type="number"
        class="form-control form-control-bordered"
      />
      <div class="text-right">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button @click="saveButton()"> Save </Button>
      </div>
    </template>

    <template v-if="type === 'nested'">
      <div
        class="col-span-6 space-y-2 rounded border border-gray-200 p-2 pl-10"
      >
        <div class="flex flex-col space-y-2">
          <div
            v-for="(clause, index) in config.clauses"
            :key="index"
            class="flex space-x-2"
          >
            <select
              v-model="clause.boolean"
              class="form-control form-control-bordered"
            >
              <option>and</option>
              <option>or</option>
            </select>
            <div class="flex-1">
              <WhereClause @delete="clause.clauses.splice(index, 1)" />
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <Button
            variant="outline"
            @click="
              () =>
                config.clauses.push({
                  boolean: 'or',
                  config: {},
                })
            "
          >
            Add Clause
          </Button>
        </div>
      </div>

      <div class="col-span-6 flex justify-end">
        <Button
          variant="outline"
          class="mr-2"
          @click="$emit('delete')"
        >
          X
        </Button>
        <Button>Save</Button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.form-control {
  width: auto;
}
</style>
