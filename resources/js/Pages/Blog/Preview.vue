<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { onMounted } from 'vue';
import { BlogPage } from '@/types/BlogTypes';
import RenderedString from '@/Components/RenderedString.vue';
import { loadScript } from '@/helpers';

const props = defineProps<{
  blog: BlogPage;
}>();

onMounted(() => {
  if (props.blog.hasTwitterEmbed) {
    loadScript('https://platform.twitter.com/widgets.js');
  }
});
</script>

<template>
  <div class="mb-4 bg-yellow-100 px-4 py-3 text-sm font-semibold text-yellow-800 shadow">
    Preview mode — this blog has not been published.
  </div>

  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>
      {{ blog.title }}
    </Heading>

    <p
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-html="blog.description"
    />

    <div
      class="-m-4 -mb-4! flex flex-col space-y-4 bg-grey-light p-4 text-sm shadow-inner"
    >
      <div v-if="blog.tags.length">
        <strong>Tagged With</strong>
        <ul class="flex flex-wrap space-x-1">
          <li
            v-for="tag in blog.tags"
            :key="tag.slug"
            class="after:content-[','] last:after:content-['']"
          >
            <span class="font-semibold text-primary-dark">{{ tag.tag }}</span>
          </li>
        </ul>
      </div>

      <div>
        <p>Published {{ blog.published }}</p>
      </div>
    </div>
  </Card>

  <Card
    v-if="blog.image"
    no-padding
  >
    <img
      :alt="blog.title"
      :src="blog.image"
      loading="lazy"
    />
  </Card>

  <Card>
    <div class="prose prose-lg max-w-none md:prose-xl">
      <RenderedString :content="blog.body" />
    </div>
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
</template>
