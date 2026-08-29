<script lang="ts" setup>
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import Card from '@/Components/Card.vue';
import { router } from '@inertiajs/vue3';
import Comments from '@/Components/PageSpecific/Shared/Comments.vue';
import { onMounted, ref, Ref } from 'vue';
import { BlogPage, RelatedBlogSimpleCard } from '@/types/BlogTypes';
import { PaginatedResponse } from '@/types/GenericTypes';
import { Comment } from '@/types/Types';
import RenderedString from '@/Components/RenderedString.vue';
import { Page } from '@inertiajs/core';
import { loadScript } from '@/helpers';
import FeaturedInCollectionCard from '@/Components/PageSpecific/Shared/FeaturedInCollectionCard.vue';
import AuthorCard from '@/Components/PageSpecific/Shared/AuthorCard.vue';
import ReadingProgressBar from '@/Components/ReadingProgressBar.vue';
import BlogArticleHeader from '@/Components/PageSpecific/Blogs/BlogArticleHeader.vue';
import BlogSidebar from '@/Components/PageSpecific/Blogs/BlogSidebar.vue';

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
</script>

<template>
  <ReadingProgressBar
    v-if="articleElem"
    :article="articleElem"
  />

  <BlogArticleHeader :blog="blog" />

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

    <BlogSidebar :related-blogs="relatedBlogs">
      <FeaturedInCollectionCard
        v-if="blog.featured_in?.length"
        :collections="blog.featured_in"
        title="This blog is featured in"
      />
    </BlogSidebar>
  </div>
</template>
