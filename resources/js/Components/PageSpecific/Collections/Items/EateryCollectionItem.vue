<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { EateryCollectionItem } from '@/types/CollectionTypes';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { pluralise } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';

defineProps<{ item: EateryCollectionItem }>();
</script>

<template>
  <Card
    ref="card"
    class="flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4"
  >
    <div class="md:max-w-16 md:min-w-1/4">
      <StaticMap
        map-classes="aspect-[1200/630]"
        :lat="item.location.lat"
        :lng="item.location.lng"
      />
    </div>

    <div class="mt-4 flex flex-1 flex-col space-y-3 md:mt-0">
      <div class="flex flex-col space-y-1">
        <Link
          :href="item.link"
          prefetch
        >
          <h2
            class="text-xl font-semibold text-primary-dark transition hover:text-grey-dark md:text-2xl"
            v-text="item.name"
          />
        </Link>

        <h3
          class="font-semibold md:text-lg"
          v-text="item.full_location"
        />

        <span
          class="prose max-w-none text-sm md:text-base"
          v-text="item.location.address"
        />
      </div>

      <div class="flex flex-1">
        <p
          class="prose max-w-none md:prose-lg"
          v-html="item.description"
        />
      </div>

      <div class="flex flex-1 items-end justify-between">
        <div
          v-if="item.reviews.number > 0"
          class="flex items-center justify-between sm:flex-col-reverse sm:items-start"
        >
          <span class="flex-1 sm:mt-2 md:text-lg">
            Rated <strong>{{ item.reviews.average }} stars</strong> from
            <strong>
              {{ item.reviews.number }}
              {{ pluralise('review', item.reviews.number) }}
            </strong>
          </span>

          <StarRating
            :rating="item.reviews.average"
            show-all
          />
        </div>

        <div
          class="rounded-lg bg-primary-light/50 px-4 py-2 text-sm leading-none font-semibold md:text-base"
        >
          <span v-text="item.type" />
        </div>
      </div>
    </div>
  </Card>
</template>
