<script setup lang="ts">
import { NearbyEatery } from '@/types/EateryTypes';
import { Deferred, Link } from '@inertiajs/vue3';
import Loader from '@/Components/Loader.vue';
import Card from '@/Components/Card.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import StarRating from '@/Components/StarRating.vue';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{ eateryName: string; nearbyEateries?: NearbyEatery[] }>();
</script>

<template>
  <Deferred data="nearbyEateries">
    <template #fallback>
      <Loader
        :absolute="false"
        color="primary"
        size="size-12"
        display
        class="py-12"
      />
    </template>

    <template v-if="nearbyEateries && nearbyEateries.length">
      <Card class="lg:rounded-lg lg:p-8">
        <SubHeading>Other locations near {{ eateryName }}</SubHeading>

        <p class="prose-lg mt-4 max-w-none lg:prose-xl">
          Not sure about eating at {{ eateryName }}? Here are some other
          locations within half a mile of {{ eateryName }} that you might enjoy!
        </p>
      </Card>

      <div class="grid gap-3 sm:mx-3 sm:grid-cols-2 2xl:mx-0 2xl:grid-cols-4">
        <Card
          v-for="nearbyEatery in nearbyEateries"
          :key="nearbyEatery.id"
          class="mx-3 flex flex-col space-y-2 sm:mx-0"
        >
          <div class="flex flex-col space-y-1">
            <div class="flex items-end justify-between">
              <h3
                class="text-xl font-semibold"
                v-text="nearbyEatery.name"
              />
              <div class="text-sm text-grey-dark">
                {{ nearbyEatery.distance.toFixed(2) }}m away
              </div>
            </div>

            <div
              v-if="nearbyEatery.ratings_count > 0"
              class="flex items-center justify-between gap-2"
            >
              <span class="flex-1">
                Rated
                <strong>{{ nearbyEatery.average_rating }} stars</strong> from
                <strong
                  >{{ nearbyEatery.ratings_count }} review{{
                    nearbyEatery.ratings_count > 1 ? 's' : ''
                  }}</strong
                >
              </span>

              <StarRating
                :rating="nearbyEatery.average_rating"
                show-all
                size="size-4"
              />
            </div>

            <div
              v-else
              class="flex items-center justify-between space-x-2"
            >
              <span class="text-grey-dark italic">
                {{ nearbyEatery.name }} doesnt have a rating yet...
              </span>

              <div>
                <CoeliacButton
                  size="sm"
                  label="Review..."
                  theme="light"
                  :as="Link"
                  :href="`${nearbyEatery.link}#leave-review`"
                />
              </div>
            </div>
          </div>

          <div class="h-px w-full bg-primary" />

          <div class="flex-1">
            <p
              class="prose max-w-none"
              v-text="nearbyEatery.info"
            />
          </div>

          <p
            class="prose-sm max-w-none"
            v-text="nearbyEatery.address"
          />

          <div class="">
            <CoeliacButton
              :as="Link"
              :label="`Read more about ${nearbyEatery.name}`"
              theme="secondary"
              bold
              size="md"
              :href="nearbyEatery.link"
            />
          </div>
        </Card>
      </div>
    </template>
  </Deferred>
</template>
