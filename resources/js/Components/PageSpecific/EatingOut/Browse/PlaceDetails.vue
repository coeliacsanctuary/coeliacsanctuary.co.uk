<script setup lang="ts">
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import Card from '@/Components/Card.vue';
import { computed, Ref, ref, watch } from 'vue';
import Loader from '@/Components/Loader.vue';
import { EateryBrowseDetails } from '@/types/EateryTypes';
import StarRating from '@/Components/StarRating.vue';
import axios, { AxiosResponse } from 'axios';
import { LinkIcon } from '@heroicons/vue/24/solid';
import { pluralise } from '@/helpers';
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

const props = withDefaults(
  defineProps<{
    show: boolean;
    placeId: number;
    branchId?: number;
  }>(),
  {
    show: false,
    branchId: undefined,
  },
);

const emits = defineEmits(['close']);

const isLoading = ref(true);

const placeDetails: Ref<undefined | EateryBrowseDetails> = ref();

watch(
  () => props.show,
  () => {
    if (!props.show) {
      return;
    }

    let url = `/api/wheretoeat/${props.placeId}`;

    if (props.branchId) {
      url += `/?branchId=${props.branchId}`;
    }

    axios.get(url).then((response: AxiosResponse<EateryBrowseDetails>) => {
      placeDetails.value = response.data;
      isLoading.value = false;
    });
  },
);

const closeSidebar = () => {
  emits('close');

  isLoading.value = true;
};

const icon = computed((): string => {
  if (placeDetails.value?.type === 'Hotel / B&B') {
    return 'hotel';
  }

  if (placeDetails.value?.type === 'Attraction') {
    return 'attraction';
  }

  return 'eatery';
});
</script>

<template>
  <Sidebar
    :open="show"
    side="left"
    size="2xl"
    @close="closeSidebar()"
  >
    <Card class="flex h-screen flex-col space-y-3 p-0!">
      <Loader
        class="z-50"
        size="size-16"
        width="border-8"
        color="primary"
        :display="isLoading && !placeDetails"
      />

      <template v-if="placeDetails && !isLoading">
        <div class="flex flex-col space-y-3 sm:space-y-5">
          <div
            class="flex w-full items-center justify-between border-b border-grey-off bg-grey-light p-3"
          >
            <h2 class="text-xl font-semibold">Place Details</h2>
          </div>

          <div class="flex justify-between px-3">
            <div class="mb-4 flex flex-1 flex-col space-y-2">
              <div class="flex w-full justify-between">
                <h2
                  class="mr-8 text-2xl font-semibold"
                  v-text="placeDetails.name"
                />

                <Icon
                  :name="icon"
                  class="h-10 w-10 text-primary"
                />
              </div>

              <div class="flex flex-col xxs:flex-row xxs:justify-between">
                <div
                  class="flex flex-col space-y-1 text-sm font-semibold text-grey-darker"
                >
                  <span
                    class="text-base"
                    v-text="
                      placeDetails.full_location.includes('Nationwide')
                        ? 'Nationwide Chain'
                        : placeDetails.full_location
                    "
                  />
                  <div>
                    <span>
                      {{ placeDetails.venue_type
                      }}{{
                        placeDetails.cuisine ? `, ${placeDetails.cuisine}` : ''
                      }}
                    </span>
                  </div>
                </div>

                <div>
                  <a
                    v-if="placeDetails.website"
                    class="mt-2 inline-flex items-center rounded-full bg-primary-light/90 px-3 py-1 text-xs leading-none font-semibold text-black transition-all ease-in-out hover:bg-primary-light/100"
                    :href="placeDetails.website"
                    target="_blank"
                  >
                    <LinkIcon class="mr-2 h-4 w-4" />

                    Visit Website
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="placeDetails.restaurants.length"
            class="flex flex-col space-y-3 p-3"
          >
            <div
              v-for="restaurant in placeDetails.restaurants"
              :key="restaurant.name"
            >
              <h4
                v-if="restaurant.name"
                class="font-semibold"
                v-text="restaurant.name"
              />

              <p
                class="prose prose-sm max-w-none sm:max-lg:prose-base lg:prose-lg"
                v-text="restaurant.info"
              />
            </div>
          </div>

          <div
            v-else
            class="px-3"
          >
            <p
              class="prose prose-sm max-w-none sm:max-lg:prose-base lg:prose-lg"
              v-text="placeDetails.info"
            />
          </div>

          <div class="mt-2 flex flex-col px-3 font-semibold text-grey-darkest">
            <span
              class="block"
              v-text="placeDetails.location.address"
            />
            <span v-text="placeDetails.phone" />
          </div>

          <div class="mt-2 flex w-full flex-col space-y-3 px-3">
            <div
              v-if="placeDetails.reviews.number > 0"
              class="flex items-center justify-between sm:flex-row-reverse"
            >
              <span class="flex-1 sm:ml-4 sm:text-lg">
                Rated
                <strong v-text="`${placeDetails.reviews.average} stars`" /> from
                <strong
                  v-text="
                    `${placeDetails.reviews.number} ${pluralise(
                      'review',
                      placeDetails.reviews.number,
                    )}`
                  "
                />
              </span>

              <StarRating
                :rating="placeDetails.reviews.average"
                half-star="star-half-alt"
                size="w-4 h-4 md:w-6 md:h-6"
                show-all
              />
            </div>

            <div
              class="mt-8! rounded-sm bg-linear-to-br from-primary/30 to-primary-light/30 text-center transition duration-500 hover:from-primary/50 hover:to-primary-light/50 md:p-4 md:text-lg"
            >
              <Link
                :href="placeDetails.link"
                class="block p-2"
                prefetch
              >
                Read more about <strong v-text="placeDetails.name" />
                {{
                  placeDetails.reviews.number > 0
                    ? ' read experiences from other people'
                    : ''
                }}
                and leave your review.
              </Link>
            </div>
          </div>
        </div>
      </template>
    </Card>
  </Sidebar>
</template>
