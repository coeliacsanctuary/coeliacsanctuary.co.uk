<template>
  <div
    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded divide-y divide-gray-200 dark:divide-gray-700"
  >
    <div
      class="cb-group-header flex items-center bg-gray-50 dark:bg-gray-800 py-2 px-3 rounded-t"
      @click="emitter('toggle')"
    >
      <Icon
        :name="collapsed ? 'chevron-right' : 'chevron-down'"
        type="micro"
        class="mr-2 text-gray-500"
      />

      <span class="font-bold text-sm text-gray-700 dark:text-gray-200">
        {{ title || 'Untitled group' }}
      </span>

      <span class="ml-2 text-xs text-gray-500">{{ itemCountLabel }}</span>

      <div class="ml-auto flex items-center space-x-2" @click.stop>
        <Button
          v-if="sortable"
          as="div"
          size="small"
          icon="arrow-up"
          variant="ghost"
          padding="tight"
          @click="emitter('move-up', index)"
        />

        <Button
          v-if="sortable"
          as="div"
          size="small"
          icon="arrow-down"
          variant="ghost"
          padding="tight"
          @click="emitter('move-down', index)"
        />

        <Button
          as="div"
          size="small"
          icon="trash"
          variant="ghost"
          padding="tight"
          @click="beforeRemove"
        />
      </div>
    </div>

    <div
      v-show="!collapsed"
      class="divide-y divide-gray-100 dark:divide-gray-700"
    >
      <div
        v-for="(nestedField, nestedFieldIndex) in item.fields"
        :key="nestedField.uniqueKey"
      >
        <component
          :is="`form-${nestedField.component}`"
          :ref="(el) => registerField(nestedField.attribute, el)"
          :field="nestedField"
          :index="nestedFieldIndex"
          :errors="errors"
          :show-help-text="true"
          :nested="true"
          :resource-name="resourceName"
          :resource-id="resourceId"
          :shown-via-new-relation-modal="shownViaNewRelationModal"
          :via-resource="viaResource"
          :via-resource-id="viaResourceId"
          :via-relationship="viaRelationship"
          @field-changed="syncTitle"
          @rows-changed="itemCount = $event"
          @file-deleted="emitter('file-deleted')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Button, Icon } from 'laravel-nova-ui'
import { ref, provide, computed, inject } from 'vue'

const props = defineProps({
  field: { type: Object, required: true },
  index: { type: Number, required: true },
  item: { type: Object, required: true },
  errors: { type: Object, required: true },
  sortable: { type: Boolean, default: false },
  collapsed: { type: Boolean, default: false },
  viaParent: { type: String, default: null },
})

const emitter = defineEmits([
  'toggle',
  'remove',
  'move-up',
  'move-down',
  'file-deleted',
])

provide(
  'viaParent',
  computed(() => props.viaParent)
)
provide(
  'index',
  computed(() => props.index)
)

const resourceName = inject('resourceName')
const resourceId = inject('resourceId')
const shownViaNewRelationModal = inject('shownViaNewRelationModal')
const viaResource = inject('viaResource')
const viaResourceId = inject('viaResourceId')
const viaRelationship = inject('viaRelationship')

const fieldFor = (attribute) =>
  props.item.fields.find((field) => field.attribute === attribute)

const title = ref(fieldFor('title')?.value ?? '')
const itemCount = ref((fieldFor('items')?.value ?? []).length)

const itemCountLabel = computed(() =>
  itemCount.value === 1 ? '1 item' : `${itemCount.value} items`
)

const fieldInstances = {}

const registerField = (attribute, el) => {
  fieldInstances[attribute] = el
}

const syncTitle = () => {
  title.value = fieldInstances.title?.value ?? ''
}

const beforeRemove = () => {
  if (confirm('Are you sure you want to remove this group?')) {
    emitter('remove', props.index)
  }
}
</script>
