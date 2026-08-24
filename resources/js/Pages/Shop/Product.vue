<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  ShopProductDetail,
  ShopProductReview,
  ShopTravelCardProductDetail,
} from '@/types/Shop';
import { PaginatedResponse } from '@/types/GenericTypes';
import StarRating from '@/Components/StarRating.vue';
import { computed, onMounted, Ref, ref, useTemplateRef, watch } from 'vue';
import { useElementVisibility } from '@vueuse/core';
import { router, usePage } from '@inertiajs/vue3';
import ProductReviews from '@/Components/PageSpecific/Shop/ProductReviews.vue';
import ProductAddBasketForm from '@/Components/PageSpecific/Shop/ProductAddBasketForm.vue';
import { pluralise } from '@/helpers';
import { Page } from '@inertiajs/core';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';
import useBrowser from '@/composables/useBrowser';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import { CustomComponent } from '@/types/Types';
import TravelCardProductCountries from '@/Components/PageSpecific/Shop/TravelCardProductCountries.vue';
import StandardTravelCardEnglishTranslation from '@/Components/PageSpecific/Shop/StandardTravelCardEnglishTranslation.vue';
import CoeliacPlusTravelCardEnglishTranslation from '@/Components/PageSpecific/Shop/CoeliacPlusTravelCardEnglishTranslation.vue';
import ProductAiOverview from '@/Components/PageSpecific/Shop/ProductAiOverview.vue';
import ProductImageModal from '@/Components/PageSpecific/Shop/ProductImageModal.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import TravelCardImportantInformation from '@/Components/PageSpecific/Shop/TravelCardImportantInformation.vue';
import ProductLongDescription from '@/Components/PageSpecific/Shop/ProductLongDescription.vue';
import ProductJumpNav from '@/Components/PageSpecific/Shop/ProductJumpNav.vue';
import ShopDeliveryFacts from '@/Components/PageSpecific/Shop/ShopDeliveryFacts.vue';
import ProductStickyBuyBar from '@/Components/PageSpecific/Shop/ProductStickyBuyBar.vue';
import FaqCard from '@/Components/PageSpecific/Shared/FaqCard.vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

type Product = ShopProductDetail | ShopTravelCardProductDetail;

const props = defineProps<{
  product: Product;
  reviews: PaginatedResponse<ShopProductReview>;
  currentReviewFilter: StarRatingType | undefined;
}>();

const isTravelCardProduct = (
  product: Product,
): product is ShopTravelCardProductDetail =>
  (product as ShopTravelCardProductDetail).is_travel_card;

const travelCardProduct = computed(() =>
  isTravelCardProduct(props.product) ? props.product : undefined,
);

const englishTranslationComponent = computed((): CustomComponent => {
  if (props.product.category.title.toLowerCase().includes('coeliac+')) {
    return CoeliacPlusTravelCardEnglishTranslation as CustomComponent;
  }

  return StandardTravelCardEnglishTranslation as CustomComponent;
});

const hasCountries = computed(
  () => (travelCardProduct.value?.countries.length ?? 0) > 0,
);

// Undefined rather than a zero-count object, so the template narrows on a single v-if.
const rating = computed(() =>
  props.product.rating?.count ? props.product.rating : undefined,
);

const jumpSections = computed(() =>
  [
    { id: 'description', label: 'Description' },
    hasCountries.value ? { id: 'where-to-use', label: 'Where to use it' } : null,
    travelCardProduct.value
      ? { id: 'what-it-says', label: 'What it says' }
      : null,
    props.product.faqs ? { id: 'questions', label: 'Questions' } : null,
    rating.value ? { id: 'reviews', label: 'Reviews' } : null,
  ].filter((section) => section !== null),
);

const viewImage = ref<false | number>(false);

const reviewFilter = ref<undefined | StarRatingType>(props.currentReviewFilter);

const allReviews: Ref<PaginatedResponse<ShopProductReview>> = ref(
  props.reviews,
);

const loadReviews = (
  url: string,
  then: (
    event: Page<{ reviews: PaginatedResponse<ShopProductReview> }>,
  ) => void,
) => {
  router.get(
    url,
    { reviewFilter: reviewFilter.value },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['reviews'],
      replace: true,
      onSuccess: then,
    },
  );
};

watch(reviewFilter, () => {
  const url = usePage().url;

  loadReviews(
    url,
    (event: Page<{ reviews: PaginatedResponse<ShopProductReview> }>) => {
      // eslint-disable-next-line no-restricted-globals
      useBrowser().replaceHistory(url, null);

      allReviews.value.data = event.props.reviews.data;
      allReviews.value.links = event.props.reviews.links;
      allReviews.value.meta = event.props.reviews.meta;

      return false;
    },
  );
});

const loadMoreReviews = () => {
  if (!props.reviews.links.next) {
    return;
  }

  const url = usePage().url;

  loadReviews(
    props.reviews.links.next,
    (event: Page<{ reviews: PaginatedResponse<ShopProductReview> }>) => {
      // eslint-disable-next-line no-restricted-globals
      useBrowser().replaceHistory(url, null);

      if (!event.props.reviews) {
        return true;
      }

      allReviews.value.data.push(...event.props.reviews.data);
      allReviews.value.links = event.props.reviews.links;
      allReviews.value.meta = event.props.reviews.meta;

      return false;
    },
  );
};

const showAiOverview = ref(true);

const buyBox = useTemplateRef<HTMLElement>('buyBox');

const buyBoxIsVisible = useElementVisibility(buyBox);

// useElementVisibility starts false, so gate on mount to stop the bar flashing in on first paint.
const hasMounted = ref(false);

onMounted(() => (hasMounted.value = true));

const showStickyBar = computed(() => hasMounted.value && !buyBoxIsVisible.value);

useJourneyTracking().logWhenVisible(
  useTemplateRef('description'),
  'scrolled_into_view',
  'ShopProduct/Description',
  {
    title: props.product.title,
  },
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('reviews'),
  'scrolled_into_view',
  'ShopProduct/Reviews',
  {
    title: props.product.title,
  },
);
</script>

<template>
  <Card class="mt-3 space-y-4">
    <Heading
      :back-link="{
        label: `Back to <strong>${product.category.title}</strong>`,
        href: product.category.link,
        position: 'top',
        direction: 'left',
      }"
    >
      {{ product.title }}
    </Heading>

    <div class="gap-6 lg:grid lg:grid-cols-5 lg:gap-8">
      <!-- Gallery -->
      <div class="flex flex-col space-y-3 lg:col-span-3">
        <button
          type="button"
          class="cursor-zoom-in overflow-hidden rounded-lg lg:min-h-0 lg:flex-1"
          :aria-label="`View a larger image of ${product.title}`"
          @click="viewImage = -1"
        >
          <!-- Fills the column height on desktop so a single wide image doesn't leave a gap
               below it next to the taller buy box. -->
          <img
            :src="product.image"
            :alt="product.title"
            width="1200"
            height="630"
            fetchpriority="high"
            class="aspect-[1200/630] w-full object-cover object-center lg:aspect-auto lg:h-full"
          />
        </button>

        <div
          v-if="product.additional_images?.length"
          class="grid grid-cols-4 gap-2 sm:grid-cols-5"
        >
          <button
            v-for="(image, index) in product.additional_images"
            :key="image"
            type="button"
            class="cursor-zoom-in overflow-hidden rounded-lg"
            :aria-label="`View image ${index + 1} of ${product.title}`"
            @click="
              () => {
                viewImage = index;

                useJourneyTracking().logEvent(
                  'clicked',
                  'ShopProduct/ViewImage',
                  { image: index },
                );
              }
            "
          >
            <img
              :src="image"
              :alt="`${product.title} — image ${index + 1}`"
              width="300"
              height="300"
              loading="lazy"
              class="aspect-square w-full object-cover object-center"
            />
          </button>
        </div>
      </div>

      <!-- Buy box -->
      <div
        id="buy-box"
        ref="buyBox"
        class="mt-6 scroll-mt-20 md:scroll-mt-32 lg:col-span-2 lg:mt-0 lg:self-start"
      >
        <div
          class="space-y-4 rounded-sm border border-primary-light/60 bg-primary-lightest/50 p-4"
        >
          <div class="flex flex-col">
              <p
                v-if="product.prices.old_price"
                class="text-sm"
              >
                was
                <span
                  class="font-semibold text-red line-through"
                  v-text="product.prices.old_price"
                />
                now
              </p>

              <p
                class="text-3xl leading-none font-semibold xs:text-4xl"
                v-text="product.prices.current_price"
              />
            </div>

            <a
              v-if="rating"
              href="#reviews"
              class="group flex items-center space-x-2"
            >
              <StarRating
                size="size-5"
                :rating="rating.average"
                show-all
              />

              <span
                class="text-sm font-semibold text-grey-dark group-hover:text-primary-dark"
              >
                {{ rating.count }}
                {{ pluralise('review', rating.count) }}
              </span>
            </a>

            <div
              class="prose max-w-none border-t border-primary-light/60 pt-4 md:prose-lg"
              v-html="product.description"
            />

          <ProductAddBasketForm :product="product" />

          <TravelCardImportantInformation v-if="travelCardProduct" />
        </div>
      </div>
    </div>
  </Card>

  <ProductJumpNav :sections="jumpSections" />

  <div
    ref="description"
    id="description"
    class="scroll-mt-20 md:scroll-mt-32"
  >
    <ProductLongDescription :description="product.long_description" />
  </div>

  <div
    v-if="travelCardProduct && hasCountries"
    id="where-to-use"
    class="scroll-mt-20 md:scroll-mt-32"
  >
    <TravelCardProductCountries
      :countries="travelCardProduct.countries"
      :product="travelCardProduct.title"
    />
  </div>

  <div
    v-if="travelCardProduct"
    id="what-it-says"
    class="scroll-mt-20 md:scroll-mt-32"
  >
    <Component
      :is="englishTranslationComponent"
      :product="travelCardProduct.title"
      :can-not-eat="
        travelCardProduct.title.toLowerCase().includes('chinese')
          ? 'soy sauce'
          : ''
      "
    />
  </div>

  <div
    v-if="product.faqs"
    id="questions"
    class="scroll-mt-20 md:scroll-mt-32"
  >
    <FaqCard
      :faqs="product.faqs"
      :title="`Common questions about my ${product.title}`"
    />
  </div>

  <ProductAiOverview
    v-if="showAiOverview && rating"
    :product-name="product.title"
    :product-id="product.id"
    @on-error="showAiOverview = false"
  />

  <div
    ref="reviews"
    id="reviews"
    class="scroll-mt-20 md:scroll-mt-32"
  >
    <Card class="space-y-4">
      <SubHeading
        classes="text-primary-dark flex flex-wrap items-center gap-x-4 gap-y-1"
      >
        <span>Reviews</span>

        <template v-if="rating">
          <StarRating
            size="size-5"
            :rating="rating.average"
            show-all
          />

          <span class="font-sans text-sm text-grey-dark">
            {{ rating.count }}
            {{ pluralise('review', rating.count) }}
          </span>
        </template>
      </SubHeading>

      <ProductReviews
        v-if="rating"
        :product-name="product.title"
        :reviews="allReviews"
        :rating="rating"
        :filtered-on="reviewFilter"
        @load-more="loadMoreReviews()"
        @set-rating="
          (rating: StarRatingType | undefined) =>
            (reviewFilter = reviewFilter === rating ? undefined : rating)
        "
      />

      <div
        v-else
        class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-sm bg-primary-lightest/60 p-6 text-center"
      >
        <ChatBubbleLeftRightIcon class="size-12 text-primary" />

        <p class="text-lg font-semibold">No reviews yet</p>

        <p class="max-w-md text-sm text-grey-dark">
          Nobody has reviewed my {{ product.title }} yet — every customer is
          invited to leave a review 10 days after their order has shipped, so
          check back soon!
        </p>
      </div>
    </Card>
  </div>

  <ShopDeliveryFacts />

  <ProductStickyBuyBar
    :product="product"
    :show="showStickyBar"
  />

  <ProductImageModal
    :title="product.title"
    :primary-image="product.image"
    :additional-images="product.additional_images"
    :open="viewImage !== false"
    :current-image="viewImage !== false ? viewImage : undefined"
    @close="viewImage = false"
    @change-image="(image) => (viewImage = image)"
  />
</template>
