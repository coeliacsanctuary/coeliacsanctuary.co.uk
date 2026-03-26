<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Paginator from '@/Components/Paginator.vue';
import { router } from '@inertiajs/vue3';
import { RssIcon } from '@heroicons/vue/20/solid';
import { Component, ref } from 'vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import useBrowser from '@/composables/useBrowser';
import { EateryCollectionCard as EateryCollectionCardType } from '@/types/EatingOutCollectionTypes';
import EateryCollectionCard from '@/Components/PageSpecific/EatingOut/Collections/EateryCollectionCard.vue';

defineProps<{
  collections: PaginatedResponse<EateryCollectionCardType>;
}>();

const page = ref(1);

const refreshPage = () => {
  router.get(
    useBrowser().currentPath(),
    {
      ...(page.value > 1 ? { page: page.value } : undefined),
    },
    {
      preserveState: true,
      only: ['collections'],
    },
  );
};

const gotoPage = (p: number) => {
  page.value = p;

  refreshPage();
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :custom-link="{
        label: 'RSS Feed',
        href: '/eating-out/collections/feed',
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

    <p class="prose max-w-none md:prose-lg">
      Discover our curated collections of places to eat gluten free across the
      UK! From trusted favourites to carefully selected spots worth trying, each
      collection brings together eateries we believe offer great choice, safety,
      and confidence for coeliacs, helping you find somewhere to eat with ease
      wherever you are.
    </p>

    <Paginator
      v-if="collections.meta.last_page > 1"
      :current="collections.meta.current_page"
      :to="collections.meta.last_page"
      @change="gotoPage"
    />
  </Card>

  <div class="grid gap-8 sm:gap-0 sm:max-xl:grid-cols-2 xl:grid-cols-3">
    <EateryCollectionCard
      v-for="collection in collections.data"
      :key="collection.link"
      :collection="collection"
      class="transition-duration-500 transition sm:scale-95 sm:hover:scale-100 sm:hover:shadow-lg"
    />
  </div>

  <Paginator
    v-if="collections.meta.last_page > 1"
    :current="collections.meta.current_page"
    :to="collections.meta.last_page"
    @change="gotoPage"
  />
</template>
