<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <Draggable
        v-if="rows.length > 0"
        v-model="rows"
        tag="div"
        class="cb-grid"
        filter=".cb-tile-action"
        :prevent-on-filter="false"
        :animation="150"
        ghost-class="cb-tile-ghost"
        chosen-class="cb-tile-chosen"
      >
        <ItemTile
          v-for="(row, index) in rows"
          :key="row.key"
          :row="row"
          @edit="openEdit(index)"
          @remove="removeRow(index)"
        />
      </Draggable>

      <p v-else class="text-sm text-gray-400 italic">
        No items in this group yet.
      </p>

      <div class="mt-4">
        <InvertedButton type="button" @click="openAdd">
          <span>Add Item</span>
        </InvertedButton>
      </div>

      <ItemFormModal
        v-if="draft"
        :show="showModal"
        :draft="draft"
        :editing="editingIndex !== null"
        :type-options="typeOptions"
        @save="commitDraft"
        @close="closeModal"
      />
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { VueDraggableNext } from 'vue-draggable-next'
import ItemTile from './ItemTile.vue'
import ItemFormModal from './ItemFormModal.vue'

export default {
  components: { Draggable: VueDraggableNext, ItemTile, ItemFormModal },

  mixins: [FormField, HandlesValidationErrors],

  emits: ['rows-changed'],

  data: () => ({
    rows: [],
    nextKey: 1,
    editingIndex: null,
    draft: null,
    showModal: false,
  }),

  methods: {
    fieldDefaultValue() {
      return []
    },

    setInitialValue() {
      this.rows = (this.field.value || []).map((repeatable) =>
        this.rowFromRepeatable(repeatable)
      )
    },

    rowFromRepeatable(repeatable) {
      const values = {}

      repeatable.fields.forEach((field) => {
        values[field.attribute] = field.value
      })

      const itemField = repeatable.fields.find(
        (field) => field.attribute === 'item_id'
      )

      return {
        key: `item-${this.nextKey++}`,
        type: repeatable.type,
        item_type: values.item_type ?? null,
        item_id: values.item_id ?? null,
        item_title: values.item_title ?? '',
        item_description: values.item_description ?? '',
        preview: itemField?.selected_item ?? null,
      }
    },

    blankRow() {
      return {
        key: `item-${this.nextKey++}`,
        type: this.repeatableType,
        item_type: null,
        item_id: null,
        item_title: '',
        item_description: '',
        preview: null,
      }
    },

    openAdd() {
      this.editingIndex = null
      this.draft = this.blankRow()
      this.showModal = true
    },

    openEdit(index) {
      this.editingIndex = index
      this.draft = { ...this.rows[index] }
      this.showModal = true
    },

    commitDraft() {
      if (this.editingIndex === null) {
        this.rows.push(this.draft)
        this.$emit('rows-changed', this.rows.length)
      } else {
        this.rows.splice(this.editingIndex, 1, this.draft)
      }

      this.closeModal()
    },

    closeModal() {
      this.showModal = false
      this.editingIndex = null
      this.draft = null
    },

    removeRow(index) {
      if ( ! confirm('Are you sure you want to remove this item?')) {
        return
      }

      this.rows.splice(index, 1)

      this.$emit('rows-changed', this.rows.length)
    },

    fill(formData) {
      this.rows.forEach((row, index) => {
        const attribute = `${this.fieldAttribute}[${index}]`

        formData.append(`${attribute}[type]`, row.type)
        formData.append(`${attribute}[fields][item_type]`, row.item_type ?? '')
        formData.append(`${attribute}[fields][item_id]`, row.item_id ?? '')
        formData.append(`${attribute}[fields][item_title]`, row.item_title ?? '')
        formData.append(
          `${attribute}[fields][item_description]`,
          row.item_description ?? ''
        )
      })
    },
  },

  computed: {
    repeatableType() {
      return this.currentField.repeatables[0].type
    },

    typeOptions() {
      const typeField = this.currentField.repeatables[0].fields.find(
        (field) => field.attribute === 'item_type'
      )

      return typeField?.options ?? []
    },
  },
}
</script>
