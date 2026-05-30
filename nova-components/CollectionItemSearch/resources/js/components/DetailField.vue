<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <span v-if="field.selected_item">
        {{ field.selected_item.title }}
      </span>
      <span v-else-if="field.value">
        ID: {{ field.value }}
      </span>
      <span v-else class="text-gray-400">—</span>
    </template>
  </PanelItem>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>
