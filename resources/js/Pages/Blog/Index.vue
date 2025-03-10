<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import BlogDetailCard from '@/Components/PageSpecific/Blogs/BlogDetailCard.vue';
import Heading from '@/Components/Heading.vue';
import Paginator from '@/Components/Paginator.vue';
import { Link, router } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import {
  AdjustmentsHorizontalIcon,
  ArrowUturnLeftIcon,
} from '@heroicons/vue/20/solid';
import { ref } from 'vue';
import BlogListSideBar from '@/Components/PageSpecific/Blogs/BlogListSideBar.vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import {
  BlogDetailCard as BlogDetailCardType,
  BlogTag,
  BlogTagCount,
} from '@/types/BlogTypes';

defineProps<{
  blogs: PaginatedResponse<BlogDetailCardType>;
  tags: BlogTagCount[];
  activeTag?: BlogTag;
}>();

const showTags = ref(false);

const page = ref(1);

const refreshPage = () => {
  router.get(
    '/blog',
    {
      ...(page.value > 1 ? { page: page.value } : undefined),
    },
    {
      preserveState: true,
      only: ['blogs', 'tags'],
    },
  );
};

const gotoPage = (p: number) => {
  page.value = p;

  refreshPage();
};

const openTagSidebar = (): void => {
  showTags.value = true;
};

const closeTagSidebar = (): void => {
  showTags.value = false;
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading v-if="!activeTag"> Coeliac Sanctuary Blogs </Heading>
    <Heading v-else>
      Coeliac Sanctuary Blogs tagged with {{ activeTag.tag }}
    </Heading>

    <p class="prose max-w-none md:prose-lg">
      Our motto is that we're more than just a gluten free blog, but blogs are
      still the heart and soul of Coeliac Sanctuary, we'll write about a bit of
      everything, from coeliac news, new products, guides, and more, we're sure
      you'll find something you'll love here!
    </p>

    <div class="flex justify-between">
      <div>
        <CoeliacButton
          v-if="activeTag"
          :icon="ArrowUturnLeftIcon"
          :as="Link"
          bold
          classes="cursor-pointer"
          href="/blog"
          label="Back to all Blogs"
        />
      </div>
      <div>
        <CoeliacButton
          :icon="AdjustmentsHorizontalIcon"
          as="a"
          bold
          classes="cursor-pointer"
          icon-position="right"
          label="Tags"
          @click="openTagSidebar()"
        />
      </div>
    </div>

    <Paginator
      v-if="blogs.meta.last_page > 1"
      :current="blogs.meta.current_page"
      :to="blogs.meta.last_page"
      @change="gotoPage"
    />
  </Card>

  <div class="grid gap-8 sm:gap-0 sm:max-xl:grid-cols-2 xl:grid-cols-3">
    <BlogDetailCard
      v-for="blog in blogs.data"
      :key="blog.link"
      :blog="blog"
      class="transition-duration-500 transition sm:scale-95 sm:hover:scale-100 sm:hover:shadow-lg"
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
