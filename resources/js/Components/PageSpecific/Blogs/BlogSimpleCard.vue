<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { BlogDetailCard, BlogSimpleCard } from '@/types/BlogTypes';

withDefaults(defineProps<{ blog: BlogSimpleCard; hover?: boolean }>(), {
  hover: true,
});
</script>

<template>
  <Card
    :shadow="false"
    class="transition duration-500"
    :class="{ 'scale-95 transform hover:scale-105 hover:opacity-100!': hover }"
  >
    <Link
      :href="blog.link"
      class="group -m-4"
    >
      <img
        :alt="blog.title"
        :src="blog.image"
        loading="lazy"
      />

      <h2
        class="p-2 text-center text-base font-semibold transition group-hover:text-primary-dark"
        v-text="blog.title"
      />
    </Link>
  </Card>
</template>
