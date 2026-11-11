<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import { BlogPage } from '@/types/BlogTypes';
import { pluralise } from '@/helpers';

defineProps<{ blog: BlogPage }>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4 overflow-hidden">
    <Heading
      :back-link="{
        href: '/blog',
        label: 'Back to all blogs.',
      }"
    >
      {{ blog.title }}
    </Heading>

    <div class="-mx-4">
      <img
        :alt="blog.header_image_alt_text ?? blog.title"
        :src="blog.image"
        loading="eager"
        fetchpriority="high"
        width="1200"
        height="630"
        class="aspect-[1200/630] w-full object-cover"
      />
    </div>

    <p
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-html="blog.description"
    />

    <div class="-mx-4 -mb-4 flex flex-col space-y-3 bg-primary-lightest/60 p-4">
      <ul class="flex flex-wrap gap-2">
        <li
          v-for="tag in blog.tags"
          :key="tag.slug"
        >
          <Link
            :href="`/blog/tags/${tag.slug}`"
            class="inline-block rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-sm font-semibold whitespace-nowrap transition hover:bg-secondary"
          >
            {{ tag.tag }}
          </Link>
        </li>
      </ul>

      <span class="text-sm font-semibold text-grey-darker">
        {{ blog.reading_time }} min read · {{ blog.comments_count }}
        {{ pluralise('comment', blog.comments_count) }}
      </span>

      <span class="text-xs text-grey-dark italic">
        Published {{ blog.published
        }}<template v-if="blog.updated">
          · Last updated {{ blog.updated }}
        </template>
      </span>
    </div>
  </Card>
</template>
