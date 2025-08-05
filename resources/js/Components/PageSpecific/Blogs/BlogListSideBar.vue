<script lang="ts" setup>
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BlogTagCount } from '@/types/BlogTypes';
import FormInput from '@/Components/Forms/FormInput.vue';
import useGoogleEvents from '@/composables/useGoogleEvents';
import CoeliacButton from '@/Components/CoeliacButton.vue';

const emits = defineEmits(['close']);

const props = defineProps<{
  open: boolean;
  tags: BlogTagCount[];
}>();

const emitClose = () => emits('close');

const searchText = ref('');

const tagsToDisplay = (): BlogTagCount[] => {
  let { tags } = props;

  if (searchText.value !== '') {
    tags = tags.filter((tag) =>
      tag.tag.toLowerCase().includes(searchText.value.toLowerCase()),
    );
  }

  return tags.slice(0, 15);
};

watch(
  () => props.open,
  () => {
    if (!props.open) {
      return;
    }

    useGoogleEvents().googleEvent('event', 'modules', {
      event_category: 'opened-filter-bar',
      event_label: `opened-blog-tags-filter`,
    });
  },
);
</script>

<template>
  <Sidebar
    :open="open"
    side="right"
    @close="emitClose()"
  >
    <div class="flex-1 bg-white">
      <div class="flex flex-col">
        <div class="border-b border-grey-off-light bg-grey-light p-2">
          <h3 class="text-xl font-semibold">Blog Tags</h3>
        </div>

        <div class="p-2">
          <FormInput
            id="blog-search"
            v-model="searchText"
            label=""
            hide-label
            borders
            name="search"
            placeholder="Search Tags"
            type="search"
          />
        </div>

        <ul
          v-if="tagsToDisplay().length"
          class="flex flex-col px-3"
        >
          <li
            v-for="tag in tagsToDisplay()"
            :key="tag.slug"
          >
            <Link
              :href="`/blog/tags/${tag.slug}`"
              class="flex cursor-pointer items-center justify-between border-b border-dashed border-grey-off-dark py-2 transition hover:bg-grey-light"
            >
              <span v-text="tag.tag" />
              <span
                class="text-sm text-grey"
                v-text="`${tag.blogs_count} Blogs`"
              />
            </Link>
          </li>
        </ul>

        <span
          v-else
          class="font-italic px-3"
          v-text="'No tags found...'"
        />

        <div class="flex-1 p-4">
          <CoeliacButton
            :as="Link"
            href="/blog/all-tags"
            label="All Blog Tags"
            size="lg"
            bold
            class="w-full justify-center"
          />
        </div>
      </div>
    </div>
  </Sidebar>
</template>
