<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="relative w-full">
        <p v-if="!itemType" class="text-sm text-gray-400 italic py-2">
          Select a type first
        </p>

        <template v-else>
          <input
            :id="field.attribute"
            v-show="!selectedItem"
            v-model="searchTerm"
            type="text"
            class="w-full form-control form-input form-control-bordered"
            :class="errorClasses"
            placeholder="Search…"
          />

          <div
            v-if="showResults && results.length"
            class="absolute z-50 w-full bg-white border border-gray-200 rounded shadow-lg mt-1"
          >
            <ul>
              <li
                v-for="result in results"
                :key="result.id"
                class="cursor-pointer border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors"
                @click="selectResult(result)"
              >
                <BlogRecipePreview v-if="isBlogOrRecipe" :item="result" :compact="true" />
                <EateryPreview v-else :item="result" :compact="true" />
              </li>
            </ul>
          </div>

          <div v-if="selectedItem" class="mt-3">
            <ItemPreview :item="selectedItem" :type="itemType" />
            <button
              type="button"
              class="mt-1 text-xs text-red-500 hover:text-red-700 transition-colors"
              @click="clearSelection"
            >
              Clear selection
            </button>
          </div>
        </template>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import debounce from 'lodash/debounce'
import BlogRecipePreview from './BlogRecipePreview.vue'
import EateryPreview from './EateryPreview.vue'
import ItemPreview from './ItemPreview.vue'

const BLOG_CLASS = 'App\\Models\\Blogs\\Blog'
const RECIPE_CLASS = 'App\\Models\\Recipes\\Recipe'

export default {
  components: { BlogRecipePreview, EateryPreview, ItemPreview },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    itemType: null,
    selectedId: null,
    selectedItem: null,
    searchTerm: '',
    results: [],
    showResults: false,
    typeSelectEl: null,
    siblingWrappers: [],
  }),

  computed: {
    isBlogOrRecipe() {
      return this.itemType === BLOG_CLASS || this.itemType === RECIPE_CLASS
    },
  },

  watch: {
    searchTerm(value) {
      this.search(value)
    },

    itemType(newVal, oldVal) {
      if (oldVal !== null) {
        this.clearSelection()
      }
    },
  },

  mounted() {
    this.$nextTick(() => {
      this.setupRepeaterRow()
    })
    document.addEventListener('click', this.handleOutsideClick)
  },

  beforeUnmount() {
    if (this.typeSelectEl) {
      this.typeSelectEl.removeEventListener('change', this.onTypeChange)
    }
    document.removeEventListener('click', this.handleOutsideClick)
  },

  methods: {
    setInitialValue() {
      this.selectedId = this.field.value || null
      this.selectedItem = this.field.selected_item || null
    },

    fill(formData) {
      formData.append(this.fieldAttribute, this.selectedId ?? '')
    },

    setupRepeaterRow() {
      let candidate = this.$el.parentElement

      while (candidate && candidate !== document.body) {
        const typeSelect = candidate.querySelector('select[id*="item_type"]')

        if (typeSelect) {
          this.typeSelectEl = typeSelect
          this.siblingWrappers = this.findSiblingWrappers(candidate)
          typeSelect.addEventListener('change', this.onTypeChange)
          this.itemType = typeSelect.value || null
          this.updateSiblingVisibility()
          return
        }

        candidate = candidate.parentElement
      }
    },

    findSiblingWrappers(rowContainer) {
      const children = Array.from(rowContainer.children)
      let foundSelf = false
      const siblings = []

      for (const child of children) {
        if (child.contains(this.$el)) {
          foundSelf = true
          continue
        }

        if (foundSelf) {
          siblings.push(child)
        }
      }

      return siblings
    },

    onTypeChange(event) {
      this.itemType = event.target.value || null
    },

    updateSiblingVisibility() {
      const hidden = !this.itemType || !this.selectedId

      this.siblingWrappers.forEach((el) => {
        el.style.display = hidden ? 'none' : ''
      })
    },

    handleOutsideClick(event) {
      if (this.$el && !this.$el.contains(event.target)) {
        this.showResults = false
      }
    },

    selectResult(result) {
      this.selectedId = result.id
      this.selectedItem = result
      this.searchTerm = ''
      this.results = []
      this.showResults = false
      this.emitFieldValueChange(this.fieldAttribute, result.id)
      this.updateSiblingVisibility()
    },

    clearSelection() {
      this.selectedId = null
      this.selectedItem = null
      this.searchTerm = ''
      this.results = []
      this.showResults = false
      this.emitFieldValueChange(this.fieldAttribute, null)
      this.updateSiblingVisibility()
    },

    search: debounce(function (term) {
      if (!this.itemType || !term) {
        this.results = []
        this.showResults = false
        return
      }

      Nova.request()
        .post('/nova-vendor/collection-item-search/search', {
          type: this.itemType,
          term,
        })
        .then((response) => {
          this.results = response.data
          this.showResults = true
        })
    }, 300),
  },
}
</script>
