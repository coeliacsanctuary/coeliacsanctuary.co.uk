<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div v-if="value.length > 0" class="space-y-4">
        <GroupRow
          v-for="(item, index) in value"
          :key="valueMap.get(item)"
          :item="item"
          :index="index"
          :field="currentField"
          :errors="errors"
          :sortable="value.length > 1"
          :collapsed="collapsed[valueMap.get(item)] === true"
          :via-parent="fieldAttribute"
          @toggle="toggleRow(item)"
          @remove="removeItem"
          @move-up="moveUp"
          @move-down="moveDown"
          @file-deleted="$emit('file-deleted')"
        />
      </div>

      <div
        class="text-center"
        :class="{
          'bg-gray-50 dark:bg-gray-900 rounded-lg border-4 dark:border-gray-600 border-dashed py-3':
            value.length === 0,
        }"
      >
        <InvertedButton type="button" @click="addItem">
          <span>Add Group</span>
        </InvertedButton>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { computed } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import GroupRow from './GroupRow.vue'

export default {
  components: { GroupRow },

  mixins: [FormField, HandlesValidationErrors],

  provide() {
    return {
      removeFile: this.removeFile,
      shownViaNewRelationModal: computed(() => this.shownViaNewRelationModal),
      viaResource: computed(() => this.viaResource),
      viaResourceId: computed(() => this.viaResourceId),
      viaRelationship: computed(() => this.viaRelationship),
      resourceName: computed(() => this.resourceName),
      resourceId: computed(() => this.resourceId),
    }
  },

  data: () => ({
    valueMap: new WeakMap(),
    collapsed: {},
    nextKey: 1,
  }),

  beforeMount() {
    this.value.forEach((repeatable) => {
      const key = this.trackRow(repeatable)

      this.collapsed[key] = this.value.length > 1
    })
  },

  methods: {
    fieldDefaultValue() {
      return []
    },

    trackRow(repeatable) {
      const key = `group-${this.nextKey++}`

      this.valueMap.set(repeatable, key)

      return key
    },

    toggleRow(repeatable) {
      const key = this.valueMap.get(repeatable)

      this.collapsed[key] = !this.collapsed[key]
    },

    addItem() {
      const repeatable = cloneDeep(this.currentField.repeatables[0])
      const key = this.trackRow(repeatable)

      this.collapsed[key] = false

      this.value.push(repeatable)
    },

    removeItem(index) {
      const [repeatable] = this.value.splice(index, 1)

      delete this.collapsed[this.valueMap.get(repeatable)]

      this.valueMap.delete(repeatable)
    },

    moveUp(index) {
      const item = this.value.splice(index, 1)

      this.value.splice(Math.max(0, index - 1), 0, item[0])
    },

    moveDown(index) {
      const item = this.value.splice(index, 1)

      this.value.splice(Math.min(this.value.length, index + 1), 0, item[0])
    },

    removeFile(attribute) {
      const {
        resourceName,
        resourceId,
        relatedResourceName,
        relatedResourceId,
        viaRelationship,
      } = this

      const uri =
        viaRelationship && relatedResourceName && relatedResourceId
          ? `/nova-api/${resourceName}/${resourceId}/${relatedResourceName}/${relatedResourceId}/field/${attribute}?viaRelationship=${viaRelationship}`
          : `/nova-api/${resourceName}/${resourceId}/field/${attribute}`

      Nova.request().delete(uri)
    },

    fill(formData) {
      this.finalPayload.forEach((repeatable, i) => {
        const attribute = `${this.fieldAttribute}[${i}]`

        formData.append(`${attribute}[type]`, repeatable.type)

        Object.keys(repeatable.fields).forEach((key) => {
          formData.append(
            `${attribute}[fields][${key}]`,
            repeatable.fields[key]
          )
        })
      })
    },
  },

  computed: {
    finalPayload() {
      return this.value.map((repeatable) => {
        const formData = new FormData()
        const fields = {}

        repeatable.fields.forEach((f) => f.fill && f.fill(formData))

        for (const pair of formData.entries()) {
          fields[pair[0]] = pair[1]
        }

        return { type: repeatable.type, fields }
      })
    },
  },
}
</script>
