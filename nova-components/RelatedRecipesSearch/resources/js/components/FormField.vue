<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="w-full">
        <div class="relative">
          <input
            ref="searchInput"
            :id="field.attribute"
            v-model="searchTerm"
            type="text"
            class="form-control form-control-bordered form-input w-full"
            :class="errorClasses"
            placeholder="Search for a recipe..."
            autocomplete="off"
            @focus="repositionDropdown"
          />

          <Teleport to="body">
            <div
              v-if="showResults && results.length"
              class="rrs-dropdown"
              :style="dropdownStyle"
            >
              <ul>
                <li
                  v-for="result in results"
                  :key="result.id"
                  class="rrs-result-item"
                  @click="select(result)"
                >
                  <img
                    v-if="result.image"
                    :src="result.image"
                    :alt="result.title"
                    class="rrs-thumb"
                  />
                  <div v-else class="rrs-thumb rrs-thumb--empty" />
                  <span class="text-sm" v-text="result.title" />
                </li>
              </ul>
            </div>
          </Teleport>
        </div>

        <div v-if="selectedRecipes.length" class="rrs-grid">
          <div
            v-for="(recipe, index) in selectedRecipes"
            :key="recipe.id"
            class="relative overflow-hidden rounded border border-gray-200"
          >
            <img
              v-if="recipe.image"
              :src="recipe.image"
              :alt="recipe.title"
              class="aspect-square w-full object-cover"
            />
            <div v-else class="aspect-square w-full bg-gray-100" />

            <div class="p-2">
              <p class="rrs-card-title" v-text="recipe.title" />
            </div>

            <button
              type="button"
              class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white transition-colors hover:bg-red-600"
              title="Remove"
              @click="remove(index)"
            >
              &times;
            </button>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import debounce from 'lodash/debounce'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    selectedRecipes: [],
    searchTerm: '',
    results: [],
    showResults: false,
    dropdownStyle: {},
  }),

  watch: {
    searchTerm(value) {
      this.search(value)
    },
  },

  mounted() {
    document.addEventListener('click', this.handleOutsideClick)
    window.addEventListener('scroll', this.repositionDropdown, true)
    window.addEventListener('resize', this.repositionDropdown)
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleOutsideClick)
    window.removeEventListener('scroll', this.repositionDropdown, true)
    window.removeEventListener('resize', this.repositionDropdown)
  },

  methods: {
    setInitialValue() {
      this.selectedRecipes = this.field.selected_recipes || []
    },

    fill(formData) {
      this.fillIfVisible(
        formData,
        this.fieldAttribute,
        JSON.stringify(this.selectedRecipes.map((r) => r.id)),
      )
    },

    repositionDropdown() {
      if (!this.$refs.searchInput) {
        return
      }

      const rect = this.$refs.searchInput.getBoundingClientRect()

      this.dropdownStyle = {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        zIndex: 9999,
      }
    },

    select(result) {
      if (this.selectedRecipes.some((r) => r.id === result.id)) {
        return
      }

      this.selectedRecipes.push(result)
      this.searchTerm = ''
      this.results = []
      this.showResults = false
      this.emitFieldValueChange(this.fieldAttribute, this.selectedRecipes.map((r) => r.id))
    },

    remove(index) {
      this.selectedRecipes.splice(index, 1)
      this.emitFieldValueChange(this.fieldAttribute, this.selectedRecipes.map((r) => r.id))
    },

    handleOutsideClick(event) {
      if (this.$el && !this.$el.contains(event.target)) {
        this.showResults = false
      }
    },

    search: debounce(function (term) {
      if (term === '') {
        this.results = []
        this.showResults = false
        return
      }

      this.repositionDropdown()

      const excludedIds = [
        ...this.selectedRecipes.map((r) => r.id),
        ...(this.resourceId ? [parseInt(this.resourceId)] : []),
      ]

      Nova.request()
        .post('/nova-vendor/related-recipes-search/search', { term, excluded_ids: excludedIds })
        .then((response) => {
          this.results = response.data
          this.showResults = true
        })
    }, 300),
  },
}
</script>

<style>
.rrs-dropdown {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  max-height: 320px;
  overflow-y: auto;
}

.rrs-result-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.15s;
}

.rrs-result-item:last-child {
  border-bottom: none;
}

.rrs-result-item:hover {
  background-color: #f9fafb;
}

.rrs-thumb {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.25rem;
  object-fit: cover;
  flex-shrink: 0;
}

.rrs-thumb--empty {
  background-color: #e5e7eb;
}

.rrs-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
  margin-top: 1rem;
  max-width: 768px;
}

.rrs-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.3;
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.right-1 { right: 0.25rem; }
.top-1 { top: 0.25rem; }
.flex-shrink-0 { flex-shrink: 0; }
.object-cover { object-fit: cover; }
.hover\:bg-red-600:hover { background-color: rgb(220 38 38); }
</style>
