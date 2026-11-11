<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import BlogDetailCard from '@/Components/PageSpecific/Blogs/BlogDetailCard.vue';
import Heading from '@/Components/Heading.vue';
import Loader from '@/Components/Loader.vue';
import Paginator from '@/Components/Paginator.vue';
import { Link, router } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import {
  AdjustmentsHorizontalIcon,
  ArrowUturnLeftIcon,
  RssIcon,
} from '@heroicons/vue/20/solid';
import { Component, computed, ref } from 'vue';
import BlogListSideBar from '@/Components/PageSpecific/Blogs/BlogListSideBar.vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import {
  BlogDetailCard as BlogDetailCardType,
  BlogTagCount,
} from '@/types/BlogTypes';
import useBrowser from '@/composables/useBrowser';
import { pluralise } from '@/helpers';

const props = defineProps<{
  blogs: PaginatedResponse<BlogDetailCardType>;
  tags?: BlogTagCount[];
  activeTag?: string;
}>();

const showTags = ref(false);

const isLoading = ref(false);

const gotoPage = (page: number) => {
  router.get(
    useBrowser().currentPath(),
    {
      ...(page > 1 ? { page } : undefined),
    },
    {
      only: ['blogs', 'meta'],
      preserveState: true,
      onStart: () => (isLoading.value = true),
      onFinish: () => (isLoading.value = false),
    },
  );
};

const openTagSidebar = (): void => {
  showTags.value = true;
};

const closeTagSidebar = (): void => {
  showTags.value = false;
};

const resultSummary = computed(() => {
  const { from, to, total, per_page: perPage } = props.blogs.meta;

  if (total === 0) {
    return 'No blogs found';
  }

  if (total <= perPage) {
    return `Showing ${total} ${pluralise('blog', total)}`;
  }

  return `Showing ${from} - ${to} of ${total} blogs`;
});
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      v-if="!activeTag"
      :custom-link="{
        label: 'RSS Feed',
        href: '/blog/feed',
        classes: 'font-semibold text-rss hover:text-black transition',
        newTab: true,
        position: 'bottom',
        direction: 'center',
        icon: RssIcon as Component,
        iconPosition: 'left',
      }"
    >
      Coeliac Sanctuary Blogs
    </Heading>
    <Heading v-else>
      Coeliac Sanctuary Blogs tagged with {{ activeTag }}
    </Heading>

    <div
      v-if="activeTag"
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
    >
      <p>
        All of my gluten free blog posts tagged with {{ activeTag }} —
        {{ blogs.meta.total }} in total.
      </p>
    </div>
    <div
      v-else
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
    >
      <p>
        My motto is that Coeliac Sanctuary is more than just a gluten free blog
        — but blogs are still its heart and soul, and this is where most of it
        starts. It's where I share what I've found, what I've tried, and
        everything I've picked up along the way about living gluten free with
        coeliac disease.
      </p>

      <p>
        I write about a bit of everything: coeliac news and research, new gluten
        free product launches, free from range reviews and taste tests,
        supermarket free from aisle updates, and honest write ups of whatever
        I've tried lately. There's plenty on the day to day of living with
        coeliac disease too — the awkward bits, the wins, and the things I wish
        I'd known when I was first diagnosed. Whether you're newly diagnosed,
        shopping gluten free for someone else, or you've been at this for years,
        I'm sure you'll find something you'll love here.
      </p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <p
        class="text-sm font-semibold text-grey-dark"
        v-text="resultSummary"
      />

      <div class="flex flex-wrap items-center gap-2">
        <CoeliacButton
          v-if="activeTag"
          :icon="ArrowUturnLeftIcon"
          :as="Link"
          bold
          classes="cursor-pointer"
          href="/blog"
          label="Back to all Blogs"
        />

        <CoeliacButton
          :icon="AdjustmentsHorizontalIcon"
          as="button"
          bold
          classes="cursor-pointer"
          icon-position="right"
          label="Tags"
          @click="openTagSidebar()"
        />
      </div>
    </div>
  </Card>

  <div class="relative">
    <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
      <template v-if="blogs.data.length">
        <BlogDetailCard
          v-for="(blog, index) in blogs.data"
          :key="blog.link"
          :blog="blog"
          :eager="index < 3"
          class="transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-lg"
        />
      </template>

      <Card
        v-else
        class="col-span-full items-center space-y-4"
      >
        <p class="text-center text-xl">
          <template v-if="activeTag">
            Sorry, I can't find any blogs tagged with {{ activeTag }}...
          </template>
          <template v-else>
            Sorry, I can't find any blogs at the moment...
          </template>
        </p>

        <CoeliacButton
          v-if="activeTag"
          :as="Link"
          :icon="ArrowUturnLeftIcon"
          bold
          size="lg"
          theme="light"
          href="/blog"
          label="Back to all Blogs"
        />
      </Card>
    </div>

    <Loader
      :display="isLoading"
      color="primary"
      size="size-12"
      fade
      blur
    />
  </div>

  <Paginator
    v-if="blogs.meta.last_page > 1"
    :current="blogs.meta.current_page"
    :to="blogs.meta.last_page"
    @change="gotoPage"
  />

  <BlogListSideBar
    :open="showTags"
    :tags="tags"
    @close="closeTagSidebar()"
  />
</template>
