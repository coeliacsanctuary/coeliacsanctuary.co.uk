<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import ModalHeading from '@/Components/Overlays/ModalHeading.vue';
import { computed, ref, watch } from 'vue';
import Loader from '@/Components/Loader.vue';
import { useHttp } from '@inertiajs/vue3';
import { DigitalCardReviewResponse, ShopProductRating } from '@/types/Shop';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import ProductReviews from '@/Components/PageSpecific/Shop/ProductReviews.vue';
import { DataResponse } from '@/types/GenericTypes';

const props = defineProps<{
  productName: string;
  productId: number;
  show: boolean;
}>();

defineEmits(['close']);

const loading = ref(true);

const productReview = ref<DigitalCardReviewResponse | undefined>();

const rating = computed(() =>
  productReview.value?.rating?.count ? productReview.value.rating : undefined,
);

const http = useHttp<
  { page: number; reviewFilter?: StarRatingType },
  DataResponse<DigitalCardReviewResponse>
>({
  page: 1,
  reviewFilter: undefined,
});

const loadReviews = () => {
  http.get(`/api/shop/digital-cards/${props.productId}/reviews`, {
    onSuccess: (response) => {
      if (http.page === 1) {
        productReview.value = response.data;

        loading.value = false;

        return;
      }

      if (!productReview.value) {
        return;
      }

      productReview.value.reviews.data.push(...response.data.reviews.data);
      productReview.value.reviews.links = response.data.reviews.links;
      productReview.value.reviews.meta = response.data.reviews.meta;
    },
  });
};

const filterOnRating = (filter: StarRatingType | undefined) => {
  http.reviewFilter = http.reviewFilter === filter ? undefined : filter;
  http.page = 1;
};

watch(
  () => props.show,
  () => {
    if (!props.show) {
      return;
    }

    loadReviews();
  },
);

watch(
  () => [http.reviewFilter, http.page],
  () => {
    loadReviews();
  },
);
</script>

<template>
  <Modal
    :open="show"
    size="full"
    width="w-full"
    @close="$emit('close')"
  >
    <ModalHeading :title="`Reviews of ${productName}`" />

    <div
      v-if="loading || !productReview"
      class="w-full pt-6 pb-24"
    >
      <Loader
        display
        color="primary"
        size="size-12"
        width="border-6"
        :absolute="false"
        on-top
      />
    </div>

    <div
      v-else
      ref="reviews"
      class="scroll-mt-20 md:scroll-mt-32"
    >
      <Card class="space-y-4">
        <ProductReviews
          :product-name="productName"
          :reviews="productReview.reviews"
          :rating="rating as ShopProductRating"
          :filtered-on="http.reviewFilter"
          digital-only
          @load-more="() => http.page++"
          @set-rating="filterOnRating"
        />
      </Card>
    </div>
  </Modal>
</template>
