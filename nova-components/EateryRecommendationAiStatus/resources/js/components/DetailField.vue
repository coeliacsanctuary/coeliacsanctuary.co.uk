<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <span v-if="field.status === 'none'" class="text-gray-400">—</span>
      <Loader v-else-if="field.status === 'pending'" :width="20" />
      <IconBoolean v-else-if="field.status === 'completed'" :value="true" />
      <IconBoolean v-else-if="field.status === 'failed'" :value="false" />
    </template>
  </PanelItem>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>
