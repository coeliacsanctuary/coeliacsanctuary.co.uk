<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { EateryCollectionPage } from '@/types/EatingOutCollectionTypes';
import { PaginatedCollection } from '@/types/GenericTypes';
import { EateryCollectionFilters, TownEatery } from '@/types/EateryTypes';
import { Deferred } from '@inertiajs/vue3';
import EateryCollectionsScreen from '@/Components/PageSpecific/EatingOut/Collections/EateryCollectionsScreen.vue';
import Loader from '@/Components/Loader.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { ref } from 'vue';

defineProps<{
  collection: EateryCollectionPage;
  eateries?: PaginatedCollection<TownEatery>;
  filters?: EateryCollectionFilters;
}>();

const eateriesRef = ref();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        href: '/eating-out/collections',
        label: 'Back to all collections.',
      }"
    >
      {{ collection.title }}
    </Heading>

    <p
      class="prose prose-lg max-w-none font-semibold md:prose-xl"
      v-html="collection.description"
    />

    <div
      class="-m-4 -mb-4! flex flex-col space-y-4 bg-grey-light p-4 text-sm shadow-inner"
    >
      <div>
        <p v-if="collection.updated">Last updated {{ collection.updated }}</p>
        <p>Published {{ collection.published }}</p>
      </div>
    </div>
  </Card>

  <Card no-padding>
    <img
      :alt="collection.title"
      :src="collection.image"
      loading="lazy"
    />
  </Card>

  <Card>
    <div
      class="prose prose-lg max-w-none md:prose-xl"
      v-html="collection.body"
    />
  </Card>

  <Deferred :data="['filters', 'eateries']">
    <template #fallback>
      <div class="flex xmd:space-x-2">
        <div class="hidden w-1/4 xmd:block">
          <Card class="py-16">
            <Loader
              :absolute="false"
              display
              color="dark"
              size="size-12"
              width="border-6"
            />
          </Card>
        </div>
        <div class="flex-1 xmd:w-3/4">
          <Card class="py-16">
            <Loader
              :absolute="false"
              display
              color="dark"
              size="size-12"
              width="border-6"
            />
          </Card>
        </div>
      </div>
    </template>

    <div ref="eateriesRef">
      <EateryCollectionsScreen
        v-if="eateries && filters"
        :eateries="eateries"
        :filters="filters"
      />
    </div>

    <JumpToContentButton
      v-if="eateriesRef"
      :anchor="eateriesRef"
      label="Jump to Eateries"
    />
  </Deferred>
</template>
