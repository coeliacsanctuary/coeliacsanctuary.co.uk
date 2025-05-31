<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { BlogDetailCard } from '@/types/BlogTypes';

defineProps<{ blog: BlogDetailCard }>();
</script>

<template>
  <Card>
    <div class="group flex-1">
      <Link
        :href="blog.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :alt="blog.title"
          :src="blog.image"
          loading="lazy"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link :href="blog.link">
          <h2
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="blog.title"
          />
        </Link>

        <div class="flex flex-1">
          <p
            class="prose max-w-none md:prose-lg"
            v-text="blog.description"
          />
        </div>
      </div>
    </div>

    <div class="-m-4 mt-4 flex flex-col bg-grey-light p-4 text-sm shadow-inner">
      <div class="flex flex-col">
        <h4 class="mb-1 font-semibold">Tagged With</h4>
        <ul class="flex flex-wrap gap-2 gap-y-1">
          <li
            v-for="tag in blog.tags"
            :key="tag.slug"
            class="after:content-[','] last:after:content-['']"
          >
            <Link
              :href="`/blog/tags/${tag.slug}`"
              class="font-semibold text-primary-dark transition hover:text-black"
            >
              {{ tag.tag }}
            </Link>
          </li>
        </ul>
      </div>

      <div class="mt-4 flex justify-between">
        <div>Added on {{ blog.date }}</div>

        <div>
          {{ blog.comments_count }} Comment{{
            blog.comments_count !== 1 ? 's' : ''
          }}
        </div>
      </div>
    </div>
  </Card>
</template>
