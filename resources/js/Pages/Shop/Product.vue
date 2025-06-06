<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  ProductAdditionalDetailAccordionProps,
  ShopProductDetail,
  ShopProductReview,
  ShopTravelCardProductDetail,
} from '@/types/Shop';
import { PaginatedResponse } from '@/types/GenericTypes';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import StarRating from '@/Components/StarRating.vue';
import { computed, nextTick, Ref, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Overlays/Modal.vue';
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';
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
import ProductAdditionalDetailsAccordionItem from '@/Components/PageSpecific/Shop/ProductAdditionalDetailsAccordionItem.vue';
import StandardTravelCardEnglishTranslation from '@/Components/PageSpecific/Shop/StandardTravelCardEnglishTranslation.vue';
import CoeliacPlusTravelCardEnglishTranslation from '@/Components/PageSpecific/Shop/CoeliacPlusTravelCardEnglishTranslation.vue';

type Product = ShopProductDetail | ShopTravelCardProductDetail;

const props = defineProps<{
  product: Product;
  reviews: PaginatedResponse<ShopProductReview>;
  productShippingText: string;
  currentReviewFilter: StarRatingType | undefined;
}>();

const isTravelCardProduct = (
  product: Product,
): product is ShopTravelCardProductDetail =>
  (product as ShopTravelCardProductDetail).is_travel_card;

const englishTranslationComponent = (category: string): CustomComponent => {
  if (category.toLowerCase().includes('coeliac+')) {
    return CoeliacPlusTravelCardEnglishTranslation as CustomComponent;
  }

  return StandardTravelCardEnglishTranslation as CustomComponent;
};

const travelCardProduct = computed(() =>
  isTravelCardProduct(props.product) ? props.product : undefined,
);

const additionalDetails: ProductAdditionalDetailAccordionProps[] = [
  {
    title: 'Full Description',
    content: props.product.long_description,
    openByDefault: true,
    wrapperComponent: Card as CustomComponent,
    wrapperClasses: 'mx-3 mt-0! sm:p-4',
  },
  travelCardProduct.value && travelCardProduct.value.countries.length
    ? {
        title: 'Where can I use this travel card?',
        component: TravelCardProductCountries as CustomComponent,
        props: {
          countries: travelCardProduct.value.countries,
          product: travelCardProduct.value.title,
        },
        headerComponent: Card as CustomComponent,
        headerClasses: 'mx-3 mt-0! sm:p-4 flex-row !w-auto',
        wrapperComponent: 'div',
      }
    : undefined,
  travelCardProduct.value
    ? {
        title: 'What does my travel card say?',
        component: englishTranslationComponent(
          travelCardProduct.value.category.title,
        ),
        props: {
          product: travelCardProduct.value.title,
          canNotEat: travelCardProduct.value.title
            .toLowerCase()
            .includes('chinese')
            ? 'soy sauce'
            : '',
        },
        headerComponent: Card as CustomComponent,
        headerClasses: 'mx-3 mt-0! sm:p-4 flex-row !w-auto',
        wrapperComponent: 'div',
      }
    : undefined,
  {
    title: 'Postage Information',
    content: props.productShippingText,
    wrapperComponent: Card as CustomComponent,
    wrapperClasses: 'mx-3 mt-0! sm:p-4',
  },
].filter((detail) => detail !== undefined);

const viewImage = ref(false);

const showReviews = ref(false);

const scrollToReviews = async (): Promise<void> => {
  showReviews.value = true;

  if (typeof document !== 'undefined') {
    await nextTick(() => {
      document.getElementById('reviews-dropdown')?.scrollIntoView();
    });
  }
};

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
</script>

<template>
  <Card class="m-3 flex flex-col space-y-4 p-0">
    <div class="mx-auto">
      <!-- Product details -->
      <div class="space-y-3 md:self-end lg:space-y-4">
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

        <div
          class="space-y-3 md:grid md:max-lg:grid-cols-2 md:max-lg:gap-3 lg:grid-cols-3 lg:max-2xl:gap-5 2xl:gap-7"
        >
          <!-- Product image -->
          <div
            class="cursor-zoom-in md:col-start-1"
            @click="viewImage = true"
          >
            <div class="overflow-hidden rounded-lg">
              <img
                :src="product.image"
                :alt="product.title"
                class="h-full w-full object-cover object-center"
              />
            </div>
          </div>

          <section class="flex flex-col space-y-5 lg:col-span-2">
            <div
              class="flex flex-col items-center space-y-2 border-b pb-5 md:items-start"
            >
              <div class="flex flex-col">
                <p v-if="product.prices.old_price">
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

              <div
                v-if="product.rating"
                class="group flex-1 cursor-pointer"
                @click="scrollToReviews()"
              >
                <div class="flex items-center space-x-2 font-semibold">
                  <p
                    class="text-gray-500 group-hover:text-primary-dark xs:max-xl:text-base"
                  >
                    Rated
                  </p>

                  <StarRating
                    size="w-4 h-4 xs:max-xl:w-5 xs:max-xl:h-5 xl:w-6 xl:h-6"
                    :rating="product.rating.average"
                  />

                  <p
                    class="text-gray-500 group-hover:text-primary-dark xs:max-xl:text-lg"
                  >
                    from
                    {{ product.rating.count }}
                    {{ pluralise('review', product.rating.count) }}
                  </p>
                </div>
              </div>
            </div>

            <div class="flex-1 space-y-6">
              <p
                class="prose max-w-none xs:max-xl:prose-lg xl:prose-xl"
                v-text="product.description"
              />
            </div>

            <!-- Product form -->
            <ProductAddBasketForm :product="product" />
          </section>
        </div>
      </div>
    </div>
  </Card>

  <ProductAdditionalDetailsAccordionItem
    v-for="additionalDetail in additionalDetails"
    :key="additionalDetail.title"
    v-bind="additionalDetail"
  />

  <Card class="mx-3 mt-0! mb-3 sm:p-4 lg:mt-1!">
    <Disclosure
      v-if="product.rating"
      as="div"
    >
      <h3>
        <DisclosureButton
          class="group relative flex w-full cursor-pointer items-center justify-between py-2 text-left"
          @click="showReviews = !showReviews"
        >
          <SubHeading
            as="h3"
            :classes="
              showReviews
                ? 'text-primary-dark flex items-center'
                : ' flex items-center'
            "
          >
            <span class="mr-4">Reviews</span>
            <StarRating
              size="size-4"
              :rating="product.rating.average"
              show-all
            />
            <span class="ml-2 font-sans text-sm">
              {{ product.rating.count }}
              {{ pluralise('review', product.rating.count) }}
            </span>
          </SubHeading>
          <span class="ml-6 flex items-center">
            <PlusIcon
              v-if="!showReviews"
              class="block h-6 w-6 text-gray-400 group-hover:text-gray-500"
              aria-hidden="true"
            />
            <MinusIcon
              v-else
              class="block h-6 w-6 text-indigo-400 group-hover:text-indigo-500"
              aria-hidden="true"
            />
          </span>
        </DisclosureButton>
      </h3>

      <DisclosurePanel
        v-show="showReviews"
        id="reviews-dropdown"
        as="div"
        class="pb-6"
        static
      >
        <ProductReviews
          :product-name="product.title"
          :reviews="allReviews"
          :rating="product.rating"
          :filtered-on="reviewFilter"
          @load-more="loadMoreReviews()"
          @set-rating="
            (rating: StarRatingType | undefined) =>
              (reviewFilter = reviewFilter === rating ? undefined : rating)
          "
        />
      </DisclosurePanel>
    </Disclosure>
  </Card>

  <Modal
    :open="viewImage"
    size="full"
    no-padding
    @close="viewImage = false"
  >
    <img
      :src="product.image"
      :alt="product.title"
    />

    <template #footer>
      <span
        class="text-xs"
        v-text="product.title"
      />
    </template>
  </Modal>
</template>
