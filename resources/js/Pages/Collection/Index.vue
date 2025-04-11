<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Paginator from '@/Components/Paginator.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import { CollectionDetailCard as CollectionDetailCardType } from '@/types/CollectionTypes';
import CollectionDetailCard from '@/Components/PageSpecific/Collections/CollectionDetailCard.vue';

defineProps<{ collections: PaginatedResponse<CollectionDetailCardType> }>();

const page = ref(1);

const refreshPage = (preserveScroll = true) => {
  router.get(
    '/collection',
    {
      ...(page.value > 1 ? { page: page.value } : undefined),
    },
    {
      only: ['collections'],
      preserveState: true,
      preserveScroll,
    },
  );
};

const gotoPage = (p: number) => {
  page.value = p;

  refreshPage(false);
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading :border="false"> Coeliac Sanctuary Collections </Heading>

    <Paginator
      v-if="collections.meta.last_page > 1"
      :current="collections.meta.current_page"
      :to="collections.meta.last_page"
      @change="gotoPage"
    />
  </Card>

  <div class="grid gap-8 sm:gap-0 sm:max-xl:grid-cols-2 xl:grid-cols-3">
    <CollectionDetailCard
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
