<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { Link } from '@inertiajs/vue3';
import { pluralise } from '@/helpers';

type Tag = {
  tag: string;
  blogs: number;
  link: string;
};

type TagGroup = {
  group: string;
  tags: Tag[];
};

defineProps<{
  tags: TagGroup[];
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        label: 'Back to Blogs',
        href: '/blog',
      }"
      :border="false"
    >
      All Blog Tags
    </Heading>
  </Card>

  <Card
    v-for="group in tags"
    :key="group.group"
    class="mt-3 flex flex-col space-y-4"
  >
    <SubHeading
      size="small"
      border
    >
      {{ group.group }}
    </SubHeading>

    <div
      class="mt-4 grid gap-3 sm:grid-cols-2 xmd:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5"
    >
      <Link
        v-for="tag in group.tags"
        :key="tag.tag"
        :href="tag.link"
        class="font-semibold transition hover:text-primary-dark"
      >
        {{ tag.tag }} ({{ tag.blogs }} {{ pluralise('blog', tag.blogs) }})
      </Link>
    </div>
  </Card>
</template>
