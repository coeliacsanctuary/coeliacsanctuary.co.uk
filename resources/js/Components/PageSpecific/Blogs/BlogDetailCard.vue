<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { BlogDetailCard } from '@/types/BlogTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import useSingleRowFit from '@/composables/useSingleRowFit';
import { computed, useTemplateRef } from 'vue';
import { pluralise } from '@/helpers';

const props = withDefaults(
  defineProps<{ blog: BlogDetailCard; eager?: boolean }>(),
  { eager: false },
);

const { visibleCount } = useSingleRowFit(
  useTemplateRef<HTMLElement>('tagRow'),
  () => props.blog.tags.length,
);

const tagsToDisplay = computed(() =>
  props.blog.tags.slice(0, visibleCount.value),
);

const remainingTagCount = computed(
  () => props.blog.tags.length - visibleCount.value,
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'BlogDetailCard',
  {
    title: props.blog.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="overflow-hidden"
  >
    <div class="group flex-1">
      <Link
        :href="blog.link"
        class="-m-4 mb-0 flex flex-col"
        prefetch
      >
        <img
          :alt="blog.header_image_alt_text ?? blog.title"
          :src="blog.image"
          :loading="eager ? 'eager' : 'lazy'"
          :fetchpriority="eager ? 'high' : 'auto'"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover"
        />
      </Link>

      <div class="mt-4 flex flex-1 flex-col space-y-3">
        <Link
          :href="blog.link"
          prefetch
        >
          <h2
            class="text-xl font-semibold transition group-hover:text-primary-dark hover:text-primary-dark md:text-2xl"
            v-text="blog.title"
          />
        </Link>

        <div class="flex flex-1">
          <p
            class="prose max-w-none md:prose-lg"
            v-text="blog.description"
          />
        </div>
      </div>
    </div>

    <div
      class="-mx-4 mt-4 -mb-4 flex flex-col space-y-3 bg-primary-lightest/60 p-4"
    >
      <ul
        ref="tagRow"
        class="flex gap-2 overflow-hidden"
      >
        <li
          v-for="tag in tagsToDisplay"
          :key="tag.slug"
          class="shrink-0"
        >
          <Link
            :href="`/blog/tags/${tag.slug}`"
            class="inline-block rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-sm font-semibold whitespace-nowrap transition hover:bg-secondary"
          >
            {{ tag.tag }}
          </Link>
        </li>

        <li
          v-if="remainingTagCount"
          class="shrink-0"
        >
          <Link
            :href="blog.link"
            class="inline-block px-1 py-1 text-sm whitespace-nowrap text-grey-dark transition hover:text-black"
            prefetch
          >
            +{{ remainingTagCount }} more
          </Link>
        </li>
      </ul>

      <span class="text-sm font-semibold text-grey-darker">
        {{ blog.comments_count }}
        {{ pluralise('comment', blog.comments_count) }}
      </span>

      <span class="text-xs text-grey-dark italic">
        Added on {{ blog.date }}
      </span>
    </div>
  </Card>
</template>
