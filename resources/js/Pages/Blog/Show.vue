<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link, router } from '@inertiajs/vue3';
import Comments from '@/Components/PageSpecific/Shared/Comments.vue';
import { computed, onMounted, ref, Ref } from 'vue';
import { BlogPage, RelatedBlogSimpleCard } from '@/types/BlogTypes';
import { PaginatedResponse } from '@/types/GenericTypes';
import { Comment } from '@/types/Types';
import RenderedString from '@/Components/RenderedString.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import { Page } from '@inertiajs/core';
import { loadScript } from '@/helpers';
import BlogSimpleCard from '@/Components/PageSpecific/Blogs/BlogSimpleCard.vue';
import collect, { Collection } from 'collect.js';

const props = defineProps<{
  blog: BlogPage;
  comments: PaginatedResponse<Comment>;
  relatedBlogs: RelatedBlogSimpleCard[];
}>();

const header = ref<HTMLElement>();

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
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        href: '/blog',
        label: 'Back to all blogs.',
      }"
    >
      {{ blog.title }}
    </Heading>

    <p
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="blog.description"
    />

    <div
      class="-m-4 -mb-4! flex flex-col space-y-4 bg-grey-light p-4 text-sm shadow-inner"
    >
      <div>
        <strong>Tagged With</strong>
        <ul class="flex flex-wrap space-x-1">
          <li
            v-for="tag in blog.tags"
            :key="tag.slug"
            class="after:content-[','] last:after:content-['']"
          >
            <Link
              :href="`/blog/tags/${tag.slug}`"
              class="font-semibold text-primary-dark hover:text-grey-dark"
            >
              {{ tag.tag }}
            </Link>
          </li>
        </ul>
      </div>

      <div>
        <p v-if="blog.updated">Last updated {{ blog.updated }}</p>
        <p>Published {{ blog.published }}</p>
      </div>
    </div>
  </Card>

  <Card no-padding>
    <img
      :alt="blog.title"
      :src="blog.image"
      loading="lazy"
    />
  </Card>

  <Card v-if="blog.featured_in?.length">
    <h3 class="text-base font-semibold text-grey-darkest">
      This blog was featured in
    </h3>

    <ul class="mt-2 flex flex-row flex-wrap text-sm leading-tight">
      <li
        v-for="collection in blog.featured_in"
        :key="collection.link"
        class="after:content-[','] last:after:content-['']"
      >
        <Link
          :href="collection.link"
          class="font-semibold text-primary-dark hover:text-grey-darker"
        >
          {{ collection.title }}
        </Link>
      </li>
    </ul>
  </Card>

  <div
    class="flex w-full flex-col space-y-3 lg:flex-row lg:space-y-0 lg:space-x-3"
  >
    <div class="flex-1">
      <Card>
        <div class="prose prose-lg max-w-none md:prose-xl">
          <RenderedString :content="blog.body" />
        </div>

        <GoogleAd
          :key="$page.url"
          code="6662103082"
        />
      </Card>

      <Card
        v-if="blog.show_author"
        faded
        theme="primary-light"
      >
        <div
          class="justify-center md:flex md:flex-row md:space-x-2 md:space-x-4"
        >
          <img
            alt="Alison Peters"
            class="float-left mr-2 mb-2 w-1/4 max-w-[150px] rounded-full"
            src="/images/misc/alison.png"
          />
          <div class="prose max-w-2xl md:prose-xl">
            <strong>Alison Peters</strong> has been Coeliac since June 2014 and
            launched Coeliac Sanctuary in August of that year, and since then
            has aimed to provide a one stop shop for Coeliacs, from blogs, to
            recipes, eating out guide and online shop.
          </div>
        </div>
      </Card>

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

    <div class="flex flex-1 flex-col space-y-3 lg:max-w-[350px]">
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
    </div>
  </div>
</template>
