<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import BlogSimpleCard from '@/Components/PageSpecific/Blogs/BlogSimpleCard.vue';
import { RelatedBlogSimpleCard } from '@/types/BlogTypes';
import collect, { Collection } from 'collect.js';
import { computed } from 'vue';

const props = defineProps<{ relatedBlogs: RelatedBlogSimpleCard[] }>();

type GroupedBlogs = {
  tag: RelatedBlogSimpleCard['related_tag'];
  blogs: Collection<RelatedBlogSimpleCard>;
};

const groupedRelatedBlogs = computed<GroupedBlogs[]>(() => {
  return collect(props.relatedBlogs)
    .groupBy('related_tag')
    .map((blogs, tag: string) => ({ tag, blogs }))
    .values()
    .all() as GroupedBlogs[];
});
</script>

<template>
  <aside class="flex flex-1 flex-col space-y-3 lg:max-w-[350px]">
    <template
      v-for="group in groupedRelatedBlogs"
      :key="group.tag"
    >
      <Card class="flex w-full flex-col space-y-3">
        <h3 class="text-lg font-semibold">
          Other blogs tagged with {{ group.tag }}
        </h3>

        <BlogSimpleCard
          v-for="groupBlog in group.blogs"
          :key="groupBlog.title"
          :blog="groupBlog"
          :hover="false"
        />

        <Link
          :href="group.blogs.first().related_tag_url"
          class="mt-5 text-lg font-semibold text-primary-dark hover:text-grey-darker"
        >
          View more blogs tagged with {{ group.tag }}
        </Link>
      </Card>
    </template>

    <slot />
  </aside>
</template>
