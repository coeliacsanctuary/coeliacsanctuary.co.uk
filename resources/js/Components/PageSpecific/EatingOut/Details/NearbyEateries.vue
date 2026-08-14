<script setup lang="ts">
import { NearbyEatery } from '@/types/EateryTypes';
import { Deferred, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import StarRating from '@/Components/StarRating.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { pluralise } from '@/helpers';

defineProps<{ eateryName: string; nearbyEateries?: NearbyEatery[] }>();
</script>

<template>
  <div>
    <Deferred data="nearbyEateries">
      <template #fallback>
        <Card class="flex flex-col space-y-4">
          <SubHeading
            text-size="small"
            class="pb-2"
          >
            Nearest places to {{ eateryName }}
          </SubHeading>

          <div class="grid animate-pulse gap-3">
            <div
              v-for="placeholder in 3"
              :key="placeholder"
              class="flex flex-col space-y-2 rounded-sm bg-primary-light/30 p-4"
            >
              <div class="h-5 w-3/4 rounded-sm bg-primary-light/60" />
              <div class="h-4 w-1/2 rounded-sm bg-primary-light/60" />
              <div class="h-3 w-full rounded-sm bg-primary-light/60" />
            </div>
          </div>
        </Card>
      </template>

      <Card
        v-if="nearbyEateries && nearbyEateries.length"
        class="flex flex-col space-y-4"
      >
        <SubHeading
          text-size="small"
          class="pb-2"
        >
          Nearest places to {{ eateryName }}
        </SubHeading>

        <p class="prose prose-sm max-w-none">
          Not sure about eating at {{ eateryName }}? Here are the nearest places
          to it that you might enjoy!
        </p>

        <div class="grid gap-3">
          <Card
            v-for="nearbyEatery in nearbyEateries"
            :key="nearbyEatery.id"
            class="group relative flex flex-col space-y-2 bg-linear-to-br from-primary/50 to-primary-light/50 transition hover:from-primary/30 hover:to-primary-light/30"
          >
            <Link
              class="absolute top-0 left-0 z-10 h-full w-full"
              :href="nearbyEatery.link"
              prefetch
            />

            <h3
              class="text-lg font-semibold"
              v-text="nearbyEatery.name"
            />

            <p class="text-xs font-semibold text-grey-darker">
              Around {{ nearbyEatery.distance.toFixed(1) }} miles away
            </p>

            <div
              v-if="nearbyEatery.ratings_count > 0"
              class="flex flex-col space-y-0.5"
            >
              <StarRating
                :rating="nearbyEatery.average_rating"
                show-all
                align="start"
                size="w-4 h-4"
              />

              <p class="text-xs font-semibold text-grey-dark">
                {{ nearbyEatery.average_rating }} from
                {{ nearbyEatery.ratings_count }}
                {{ pluralise('rating', nearbyEatery.ratings_count) }}
              </p>
            </div>

            <p
              v-else
              class="text-xs text-grey-dark italic"
            >
              No ratings yet &mdash; be the first to review!
            </p>

            <p
              class="text-xs text-grey-dark"
              v-text="nearbyEatery.address"
            />
          </Card>
        </div>
      </Card>
    </Deferred>
  </div>
</template>
