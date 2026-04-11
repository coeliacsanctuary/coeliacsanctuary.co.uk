<script setup>
import { ref, watch, onMounted } from 'vue';
import { orderables } from '../../data';
import { Button } from 'laravel-nova-ui';

const props = defineProps(['order']);

onMounted(() => {
  if (props.order && props.order.config) {
    config.value = props.order.config;
  }
});

const emits = defineEmits(['save', 'delete', 'edit']);

const config = ref({
  column: '',
  direction: 'asc',
  table: undefined,
  localKey: undefined,
  foreignKey: undefined,
  additional: {},
});

const directions = ['asc', 'desc'];
const hasSaved = ref(false);

const forceSave = () => {
  hasSaved.value = false;

  saveButton();
};

const saveButton = () => {
  let hasError = false;

  ['column', 'direction'].forEach((field) => {
    if (hasError) {
      return;
    }

    if (config.value[field] === '') {
      alert(`Please ensure ${field} has a value!`);

      hasError = true;
    }
  });

  if (hasError) {
    return;
  }

  if (hasSaved.value) {
    emits('edit');

    return;
  }

  emits('save', {
    config: config.value,
  });

  hasSaved.value = true;
};

defineExpose({ forceSave });

watch(
  () => config.value?.column,
  () => {
    if (config.value.column === '') {
      return;
    }

    if (props.order && props.order.config?.column === config.value.column) {
      return;
    }

    const raw = orderables.find(
      (order) => order.column === config.value.column,
    );

    config.value.table = raw.table;
    config.value.localKey = raw.localKey;
    config.value.foreignKey = raw.foreignKey;
    config.value.alias = raw.alias;
    config.value.additional = raw.additional;
  },
);
</script>

<template>
  <div class="grid grid-cols-6 gap-2">
    <select
      v-model="config.column"
      class="form-control form-control-bordered"
    >
      <option disabled>Column</option>
      <option
        v-for="field in orderables"
        :key="field.column"
        :value="field.column"
        v-text="field.label"
      />
    </select>
    <select
      v-model="config.direction"
      class="form-control form-control-bordered"
    >
      <option
        v-for="direction in directions"
        :key="direction"
        v-text="direction"
      />
    </select>
    <div />
    <div />
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
  </div>
</template>

<style scoped>
.form-control {
  width: auto;
}
</style>
