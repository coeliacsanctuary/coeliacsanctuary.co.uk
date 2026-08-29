<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { BlogPage, RelatedBlogSimpleCard } from '@/types/BlogTypes';
import RenderedString from '@/Components/RenderedString.vue';
import { loadScript } from '@/helpers';
import AuthorCard from '@/Components/PageSpecific/Shared/AuthorCard.vue';
import ReadingProgressBar from '@/Components/ReadingProgressBar.vue';
import BlogArticleHeader from '@/Components/PageSpecific/Blogs/BlogArticleHeader.vue';
import BlogSidebar from '@/Components/PageSpecific/Blogs/BlogSidebar.vue';

const props = defineProps<{
  blog: BlogPage;
  relatedBlogs: RelatedBlogSimpleCard[];
}>();

const articleElem = ref<HTMLElement>();

/**
 * This page renders in an iframe inside Nova, where following a link would
 * strand the editor on the real site with no way back to their preview.
 */
const blockNavigation = (event: MouseEvent): void => {
  if ((event.target as HTMLElement).closest('a')) {
    event.preventDefault();
    event.stopPropagation();
  }
};

onMounted(() => {
  document.addEventListener('click', blockNavigation, { capture: true });

  if (props.blog.hasTwitterEmbed) {
    loadScript('https://platform.twitter.com/widgets.js');
  }
});

onBeforeUnmount(() => {
  document.removeEventListener('click', blockNavigation, { capture: true });
});
</script>

<template>
  <div
    class="mb-4 bg-yellow-100 px-4 py-3 text-sm font-semibold text-yellow-800 shadow"
  >
    Preview mode — this blog has not been published. Comments and collections
    are not shown, and links are disabled.
  </div>

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
    </div>

    <BlogSidebar :related-blogs="relatedBlogs" />
  </div>
</template>
