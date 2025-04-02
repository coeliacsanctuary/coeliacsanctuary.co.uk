<script setup lang="ts">
import { PaginatedResponse } from '@/types/GenericTypes';
import { ShopProductRating, ShopProductReview } from '@/types/Shop';
import StarRating from '@/Components/StarRating.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ref, watch } from 'vue';
import RatingsBreakdown from '@/Components/PageSpecific/Shared/RatingsBreakdown.vue';
import { formatDate } from '@/helpers';
import Info from '@/Components/Info.vue';
import { StarRating as StarRatingType } from '@/types/EateryTypes';

const props = defineProps<{
  productName: string;
  reviews: PaginatedResponse<ShopProductReview>;
  rating: ShopProductRating;
  filteredOn?: StarRatingType;
}>();

const loading = ref(false);

watch(props.reviews, () => {
  loading.value = false;
});

const reviewFilter = ref<undefined | StarRatingType>(undefined);
const emits = defineEmits(['load-more', 'set-rating']);

const setRatingFilter = (filter: StarRatingType | undefined) => {
  reviewFilter.value = filter;

  emits('set-rating', filter);
};
</script>

<template>
  <div class="mx-auto md:grid md:max-xl:grid-cols-3 md:gap-x-8 xl:grid-cols-4">
    <RatingsBreakdown
      :average="rating.average"
      :breakdown="rating.breakdown"
      :count="rating.count"
      :can-add-review="false"
      :filtered-on="filteredOn"
      filterable
      @filter="setRatingFilter"
    >
      Have you used our <strong v-text="productName" />? Share your thoughts
      with other customers!
    </RatingsBreakdown>

    <div class="mt-8 md:max-xl:col-span-2 md:mt-0 xl:col-span-3">
      <Info class="mb-4">
        <p class="text-sm">
          All reviews are from verified purchases, customers are invited to
          leave a review 10 days* after their order has been shipped.
        </p>

        <small class="text-xs mt-4">* 10 days is for UK orders only.</small>
      </Info>

      <div class="flow-root">
        <div class="-my-6 divide-y divide-gray-200">
          <div
            v-for="review in reviews.data"
            :key="review.name"
            class="py-6"
          >
            <div class="flex items-center justify-between">
              <div class="flex flex-col">
                <h4
                  class="font-bold lg:text-xl"
                  v-text="review.name || 'Anonymous'"
                />
                <time
                  :datetime="review.date"
                  :title="formatDate(review.date, 'Do MMM YYYY h:mm a')"
                  v-text="review.date_diff"
                />
              </div>

              <div class="mt-1 flex items-center">
                <StarRating
                  :rating="review.rating"
                  size="w-4 h-4 xs:w-5 xs:h-5"
                  show-all
                />
              </div>
            </div>

            <div
              class="prose mt-2 max-w-none lg:prose-lg"
              v-html="
                review.review
                  ? review.review
                  : `<em>Customer didn't leave a review with their rating</em>`
              "
            />
          </div>

          <CoeliacButton
            v-if="reviews.links.next"
            label="Load more reviews..."
            size="xl"
            theme="light"
            :loading="loading"
            as="a"
            @click="
              loading = true;
              $emit('load-more');
            "
          />
        </div>
      </div>
    </div>
  </div>
</template>
