<script lang="ts" setup>
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link, router } from '@inertiajs/vue3';
import Comments from '@/Components/PageSpecific/Shared/Comments.vue';
import { computed, onMounted, ref, Ref } from 'vue';
import { BlogPage, RelatedBlogSimpleCard } from '@/types/BlogTypes';
import { PaginatedResponse } from '@/types/GenericTypes';
import { Comment } from '@/types/Types';
import RenderedString from '@/Components/RenderedString.vue';
import { Page } from '@inertiajs/core';
import { loadScript, pluralise } from '@/helpers';
import BlogSimpleCard from '@/Components/PageSpecific/Blogs/BlogSimpleCard.vue';
import collect, { Collection } from 'collect.js';
import FeaturedInCollectionCard from '@/Components/PageSpecific/Shared/FeaturedInCollectionCard.vue';
import AuthorCard from '@/Components/PageSpecific/Shared/AuthorCard.vue';
import ReadingProgressBar from '@/Components/ReadingProgressBar.vue';

const props = defineProps<{
  blog: BlogPage;
  comments: PaginatedResponse<Comment>;
  relatedBlogs: RelatedBlogSimpleCard[];
}>();

const header = ref<HTMLElement>();

const articleElem = ref<HTMLElement>();

const allComments: Ref<PaginatedResponse<Comment>> = ref(props.comments);
const isLoadingComments = ref(false);
const hasLoadedMoreComments = ref(false);

onMounted(() => {
  if (props.blog.hasTwitterEmbed) {
    loadScript('https://platform.twitter.com/widgets.js');
  }
});

const loadMoreComments = () => {
  if (!props.comments.links.next) {
    return;
  }

  router.get(
    props.comments.links.next,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      only: ['comments'],
      preserveUrl: true,
      onSuccess: (event: Page<{ comments?: PaginatedResponse<Comment> }>) => {
        if (event.props.comments) {
          allComments.value.data.push(...event.props.comments.data);
          allComments.value.links = event.props.comments.links;
          allComments.value.meta = event.props.comments.meta;
        }
      },
    },
  );
};

const handleCommentReset = () => {
  header.value?.scrollIntoView({ behavior: 'smooth' });

  router.get(
    props.comments.links.first,
    {},
    {
      preserveState: true,
      only: ['comments'],
      preserveUrl: true,
      onSuccess: (page: Page<{ comments?: PaginatedResponse<Comment> }>) => {
        hasLoadedMoreComments.value = false;

        if (page.props.comments) {
          allComments.value.data = page.props.comments.data;
          allComments.value.links = page.props.comments.links;
          allComments.value.meta = page.props.comments.meta;
        }
      },
    },
  );
};

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
  <ReadingProgressBar
    v-if="articleElem"
    :article="articleElem"
  />

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

  <FaqCard
    v-if="blog.faqs && blog.faq_display === 'top'"
    :faqs="blog.faqs"
    :title="`Frequently asked questions about ${blog.short_title || blog.title}`"
  />

  <div
    class="flex w-full flex-col space-y-3 lg:flex-row lg:space-y-0 lg:space-x-3"
  >
    <div class="flex-1">
      <Card>
        <div
          ref="articleElem"
          class="article-body @container prose prose-lg max-w-none md:prose-xl"
        >
          <RenderedString :content="blog.body" />
        </div>
      </Card>

      <FaqCard
        v-if="blog.faqs && (!blog.faq_display || blog.faq_display === 'bottom')"
        :faqs="blog.faqs"
        :title="`Frequently asked questions about ${blog.short_title || blog.title}`"
      />

      <AuthorCard
        v-if="blog.show_author"
        author="Alison Peters"
      />

      <Comments
        :id="blog.id"
        :comments="allComments"
        module="blog"
        :is-loading="isLoadingComments"
        :has-loaded-more="hasLoadedMoreComments"
        @load-more="loadMoreComments"
        @reset="handleCommentReset"
      />
    </div>

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

      <FeaturedInCollectionCard
        v-if="blog.featured_in?.length"
        :collections="blog.featured_in"
        title="This blog is featured in"
      />
    </aside>
  </div>
</template>
