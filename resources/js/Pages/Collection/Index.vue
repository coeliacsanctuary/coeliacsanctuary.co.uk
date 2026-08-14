<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Loader from '@/Components/Loader.vue';
import Paginator from '@/Components/Paginator.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import { CollectionDetailCard as CollectionDetailCardType } from '@/types/CollectionTypes';
import CollectionDetailCard from '@/Components/PageSpecific/Collections/CollectionDetailCard.vue';
import useBrowser from '@/composables/useBrowser';
import { pluralise } from '@/helpers';

const props = defineProps<{
  collections: PaginatedResponse<CollectionDetailCardType>;
}>();

const isLoading = ref(false);

const gotoPage = (page: number) => {
  router.get(
    useBrowser().currentPath(),
    {
      ...(page > 1 ? { page } : undefined),
    },
    {
      only: ['collections'],
      preserveState: true,
      onStart: () => (isLoading.value = true),
      onFinish: () => (isLoading.value = false),
    },
  );
};

const resultSummary = computed(() => {
  const { from, to, total, per_page: perPage } = props.collections.meta;

  if (total === 0) {
    return 'No collections found';
  }

  if (total <= perPage) {
    return `Showing ${total} ${pluralise('collection', total)}`;
  }

  return `Showing ${from} - ${to} of ${total} collections`;
});
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>Coeliac Sanctuary Collections</Heading>

    <div class="prose prose-lg max-w-none font-semibold md:prose-xl">
      <p>
        Collections are my way of pulling together the things that belong side
        by side — every gluten free recipe for one occasion, everything I've
        written on a single subject, or all the coeliac friendly places to eat
        in one city, gathered onto a page of their own.
      </p>

      <p>
        So if you're after gluten free Christmas baking, somewhere safe to eat
        on a weekend away, or everything I've picked up since being diagnosed,
        there's a good chance I've already put it together for you. Each
        collection below pulls in recipes, blog posts and places to eat from
        across the site, and I keep adding to them as the site grows.
      </p>
    </div>

    <p
      class="text-sm font-semibold text-grey-dark"
      v-text="resultSummary"
    />
  </Card>

  <div class="relative">
    <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
      <template v-if="collections.data.length">
        <CollectionDetailCard
          v-for="(collection, index) in collections.data"
          :key="collection.link"
          :collection="collection"
          :eager="index < 3"
          class="transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-lg"
        />
      </template>

      <Card
        v-else
        class="col-span-full items-center space-y-4"
      >
        <p class="text-center text-xl">
          Sorry, I can't find any collections at the moment...
        </p>
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
    v-if="collections.meta.last_page > 1"
    :current="collections.meta.current_page"
    :to="collections.meta.last_page"
    @change="gotoPage"
  />
</template>
