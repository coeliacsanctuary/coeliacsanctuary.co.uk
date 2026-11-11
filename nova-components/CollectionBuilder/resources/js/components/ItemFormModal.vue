<template>
  <Modal :show="show" size="2xl" @close-via-escape="$emit('close')">
    <form
      class="mx-auto overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
      @submit.prevent="save"
    >
      <ModalHeader>{{ editing ? 'Edit Item' : 'Add Item' }}</ModalHeader>

      <ModalContent>
        <div class="space-y-4">
          <div>
            <label class="inline-block text-sm mb-1 font-bold">Type</label>

            <select
              v-model="draft.item_type"
              class="w-full form-control form-input form-control-bordered"
              @change="clearSelection"
            >
              <option :value="null">Choose a type</option>

              <option
                v-for="option in typeOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div v-if="draft.item_type">
            <label class="inline-block text-sm mb-1 font-bold">Item</label>

            <div v-if="draft.preview" class="cb-modal-preview rounded border border-gray-200 dark:border-gray-700">
              <div v-if="isBlogOrRecipe" class="flex items-start gap-3 p-2">
                <img
                  v-if="draft.preview.imageUrl"
                  :src="draft.preview.imageUrl"
                  :alt="draft.preview.title"
                  class="cb-modal-preview-image rounded"
                />

                <div class="min-w-0">
                  <p class="text-sm font-semibold truncate">{{ draft.preview.title }}</p>

                  <p v-if="draft.preview.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
                    {{ draft.preview.description }}
                  </p>
                </div>
              </div>

              <div v-else class="p-2">
                <p class="text-sm font-semibold">{{ draft.preview.title }}</p>

                <p v-if="draft.preview.address" class="text-xs text-gray-500 mt-0.5">
                  {{ draft.preview.address }}
                </p>

                <p v-if="draft.preview.info" class="text-xs text-gray-400 mt-0.5 line-clamp-2">
                  {{ draft.preview.info }}
                </p>
              </div>
            </div>

            <button
              v-if="draft.preview"
              type="button"
              class="mt-1 text-xs text-red-500 hover:text-red-700"
              @click="clearSelection"
            >
              Clear selection
            </button>

            <div v-else>
              <input
                v-model="searchTerm"
                type="text"
                class="w-full form-control form-input form-control-bordered"
                placeholder="Search…"
              />

              <div
                v-if="results.length > 0"
                class="cb-modal-results rounded border border-gray-200 dark:border-gray-700"
              >
                <ul>
                  <li
                    v-for="result in results"
                    :key="result.id"
                    class="cursor-pointer border-b border-gray-100 last:border-b-0 hover:bg-gray-50 dark:border-gray-700"
                    @click="selectResult(result)"
                  >
                    <div v-if="isBlogOrRecipe" class="flex items-start gap-3 p-2">
                      <img
                        v-if="result.imageUrl"
                        :src="result.imageUrl"
                        :alt="result.title"
                        class="cb-modal-preview-image rounded"
                      />

                      <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ result.title }}</p>

                        <p v-if="result.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
                          {{ result.description }}
                        </p>
                      </div>
                    </div>

                    <div v-else class="p-2">
                      <p class="text-sm font-semibold">{{ result.title }}</p>

                      <p v-if="result.address" class="text-xs text-gray-500 mt-0.5">
                        {{ result.address }}
                      </p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div v-if="draft.preview">
            <label class="inline-block text-sm mb-1 font-bold">Title</label>

            <input
              v-model="draft.item_title"
              type="text"
              class="w-full form-control form-input form-control-bordered"
            />

            <p class="text-xs text-gray-500 mt-1">
              Leave blank to use the items title
            </p>
          </div>

          <div v-if="draft.preview">
            <label class="inline-block text-sm mb-1 font-bold">Description</label>

            <textarea
              v-model="draft.item_description"
              rows="4"
              class="w-full form-control form-input form-control-bordered py-3 h-auto"
            />

            <p class="text-xs text-gray-500 mt-1">
              Leave blank to use the items description
            </p>
          </div>

          <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        </div>
      </ModalContent>

      <ModalFooter>
        <div class="flex items-center ml-auto space-x-3">
          <button
            type="button"
            class="cb-cancel text-sm text-gray-500"
            @click="$emit('close')"
          >
            Cancel
          </button>

          <Button type="submit">Save</Button>
        </div>
      </ModalFooter>
    </form>
  </Modal>
</template>

<script>
import { Button } from 'laravel-nova-ui'
import debounce from 'lodash/debounce'

const BLOG_CLASS = 'App\\Models\\Blogs\\Blog'
const RECIPE_CLASS = 'App\\Models\\Recipes\\Recipe'

export default {
  components: { Button },

  props: {
    show: {
      type: Boolean,
      default: false,
    },
    draft: {
      type: Object,
      required: true,
    },
    editing: {
      type: Boolean,
      default: false,
    },
    typeOptions: {
      type: Array,
      default: () => [],
    },
  },

  emits: ['save', 'close'],

  data: () => ({
    searchTerm: '',
    results: [],
    error: null,
  }),

  computed: {
    isBlogOrRecipe() {
      return (
        this.draft.item_type === BLOG_CLASS ||
        this.draft.item_type === RECIPE_CLASS
      )
    },
  },

  watch: {
    searchTerm(term) {
      this.search(term)
    },
  },

  methods: {
    clearSelection() {
      this.draft.item_id = null
      this.draft.preview = null
      this.searchTerm = ''
      this.results = []
    },

    selectResult(result) {
      this.draft.item_id = result.id
      this.draft.preview = result
      this.searchTerm = ''
      this.results = []
      this.error = null
    },

    save() {
      if ( ! this.draft.item_type || ! this.draft.item_id) {
        this.error = 'Choose a type and select an item.'

        return
      }

      this.error = null

      this.$emit('save')
    },

    search: debounce(function (term) {
      if ( ! this.draft.item_type || ! term) {
        this.results = []

        return
      }

      Nova.request()
        .post('/nova-vendor/collection-item-search/search', {
          type: this.draft.item_type,
          term,
        })
        .then((response) => {
          this.results = response.data
        })
    }, 300),
  },
}
</script>
