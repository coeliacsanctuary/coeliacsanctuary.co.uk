<template>
  <div class="border border-gray-200 rounded bg-gray-50">
    <BlogRecipePreview v-if="isBlogOrRecipe" :item="item" />
    <EateryPreview v-else :item="item" />
  </div>
</template>

<script>
import BlogRecipePreview from './BlogRecipePreview.vue'
import EateryPreview from './EateryPreview.vue'

const BLOG_CLASS = 'App\\Models\\Blogs\\Blog'
const RECIPE_CLASS = 'App\\Models\\Recipes\\Recipe'

export default {
  components: { BlogRecipePreview, EateryPreview },

  props: {
    item: {
      type: Object,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
  },

  computed: {
    isBlogOrRecipe() {
      return this.type === BLOG_CLASS || this.type === RECIPE_CLASS
    },
  },
}
</script>
