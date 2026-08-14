<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { CollectionPage } from '@/types/CollectionTypes';
import CollectionGroupCard from '@/Components/PageSpecific/Collections/CollectionGroupCard.vue';
import RenderedString from '@/Components/RenderedString.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import useScreensize from '@/composables/useScreensize';
import { pluralise } from '@/helpers';
import { computed, ref } from 'vue';

const props = defineProps<{ collection: CollectionPage }>();

const headerImage = ref<HTMLElement>();

const collectionElem = ref<HTMLElement>();

const search = ref('');

const showFilter = computed(() => props.collection.groups.length >= 6);

const isSingleUntitledGroup = computed(
  () =>
    props.collection.groups.length === 1 &&
    !props.collection.groups[0].title &&
    !props.collection.groups[0].body,
);

const itemSummary = computed(() => {
  const counts = { recipes: 0, blogs: 0, eateries: 0 };

  props.collection.groups.forEach((group) =>
    group.items.forEach((item) => {
      if (item.type === 'Recipe') {
        counts.recipes += 1;

        return;
      }

      if (item.type === 'Blog') {
        counts.blogs += 1;

        return;
      }

      counts.eateries += 1;
    }),
  );

  return [
    counts.recipes
      ? `${counts.recipes} ${pluralise('recipe', counts.recipes)}`
      : null,
    counts.blogs ? `${counts.blogs} ${pluralise('blog', counts.blogs)}` : null,
    counts.eateries
      ? `${counts.eateries} ${counts.eateries === 1 ? 'place to eat' : 'places to eat'}`
      : null,
  ]
    .filter(Boolean)
    .join(' · ');
});

const visibleGroups = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) {
    return props.collection.groups.filter(
      (group) => group.items.length > 0 || !!group.body,
    );
  }

  return props.collection.groups
    .map((group) => {
      if (group.title?.toLowerCase().includes(term)) {
        return group;
      }

      return {
        ...group,
        items: group.items.filter((item) =>
          String(item.title ?? item.name ?? '')
            .toLowerCase()
            .includes(term),
        ),
      };
    })
    .filter((group) => group.items.length > 0);
});
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4 overflow-hidden">
    <Heading
      :back-link="{
        href: '/collection',
        label: 'Back to all collections.',
      }"
    >
      {{ collection.title }}
    </Heading>

    <div
      ref="headerImage"
      class="-mx-4"
    >
      <img
        :alt="collection.header_image_alt_text ?? collection.title"
        :src="collection.image"
        loading="eager"
        fetchpriority="high"
        width="1200"
        height="630"
        class="aspect-[1200/630] w-full object-cover"
      />
    </div>

    <div
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-text="collection.description"
    />

    <article
      v-if="collection.body"
      class="prose prose-lg max-w-none md:prose-xl"
    >
      <RenderedString :content="collection.body" />
    </article>

    <div class="-mx-4 -mb-4 flex flex-col space-y-2 bg-primary-lightest/60 p-4">
      <span
        v-if="itemSummary"
        class="text-sm font-semibold text-grey-darker"
        v-text="itemSummary"
      />

      <span class="text-xs text-grey-dark italic">
        Added {{ collection.published
        }}<template v-if="collection.updated">
          · Last updated {{ collection.updated }}
        </template>
      </span>
    </div>
  </Card>

  <div
    ref="collectionElem"
    class="flex flex-col space-y-4"
  >
    <Card v-if="showFilter">
      <FormInput
        v-model="search"
        name="search"
        label=""
        :placeholder="`Search ${collection.title}...`"
        hide-label
        borders
        class="w-full max-w-sm md:max-w-md"
        :size="useScreensize().screenIsGreaterThan('md') ? 'large' : 'default'"
      />
    </Card>

    <template v-if="visibleGroups.length">
      <CollectionGroupCard
        v-for="(group, index) in visibleGroups"
        :key="index"
        :group="group"
        :display-type="collection.display_type"
        :wrapped="!isSingleUntitledGroup"
      />
    </template>

    <Card v-else>
      <p
        v-if="search"
        class="text-center text-xl"
      >
        Sorry, I can't find anything in this collection matching "{{
          search
        }}"...
      </p>

      <p
        v-else
        class="text-center text-xl"
      >
        There's nothing in this collection at the moment...
      </p>
    </Card>
  </div>

  <JumpToContentButton
    v-if="collectionElem && headerImage"
    :anchor="collectionElem"
    :show-after="headerImage"
    label="Jump to the collection"
  />
</template>
