<script setup lang="ts">
import Card from '@/Components/Card.vue';
import BasicCountyHeading from '@/Components/PageSpecific/EatingOut/MagicRoutes/BasicCountyHeading.vue';
import {
  MagicRouteTownPage,
  MagicRouteTownPageEatery,
  TownEatery,
} from '@/types/EateryTypes';
import Heading from '@/Components/Heading.vue';
import StarRating from '@/Components/StarRating.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { pluralise } from '@/helpers';

defineProps<{
  town: MagicRouteTownPage;
  page_intro: string;
  page_outro: string;
  eateries: MagicRouteTownPageEatery[];
}>();

const eateryName = (eatery: TownEatery): string => {
  if (eatery.branch && eatery.branch.name) {
    return eatery.branch.name;
  }

  return eatery.name;
};
</script>

<template>
  <BasicCountyHeading
    :title="`Eating 100% Gluten Free in ${town.name}`"
    :county-name="town.name"
    :latlng="town.latlng"
    :back-link="town.link"
    :back-text="`Back to ${town.name}`"
    :image="town.image"
    additional-map-url="?features=100-gluten-free"
  />

  <Card>
    <div
      class="prose prose-lg max-w-none xl:prose-xl"
      v-html="page_intro"
    />
  </Card>

  <Card
    v-for="eatery in eateries"
    :key="eatery.key"
    class="flex flex-col space-y-4"
  >
    <Heading classes="text-left">{{ eateryName(eatery.details) }}</Heading>

    <div class="flex flex-col space-y-4 md:flex-row md:space-x-4">
      <div
        class="prose prose-lg mt-0 max-w-none flex-1 xl:prose-xl"
        v-html="eatery.info"
      />

      <div
        class="flex-shrink-none w-full md:max-w-3xs xmd:max-w-xs lg:max-w-sm"
      >
        <Card
          theme="primary-light"
          faded
          class="flex flex-col space-y-4"
        >
          <div v-if="eatery.details.reviews.number > 0">
            <StarRating
              :rating="eatery.details.reviews.average"
              size="size-6 xs:size-8 md:size-10"
              align="start"
              show-all
            />

            <p class="text-lg font-semibold">
              Rated {{ eatery.details.reviews.average }} stars from
              {{ eatery.details.reviews.number }}
              {{ pluralise('rating', eatery.details.reviews.number) }}
            </p>
          </div>

          <StaticMap
            :lat="eatery.details.location.lat"
            :lng="eatery.details.location.lng"
            :can-expand="false"
          />

          <p
            class="text-lg font-semibold"
            v-html="eatery.details.location.address"
          />

          <p
            v-if="eatery.details.website"
            class="text-lg font-semibold"
          >
            <a
              :href="eatery.details.website"
              target="_blank"
              class="block w-full truncate hover:underline"
              v-text="eatery.details.website"
            />
          </p>
        </Card>
      </div>
    </div>

    <div>
      <CoeliacButton
        :href="eatery.details.link"
        :label="`Read more about ${eatery.details.name}`"
        size="lg"
        bold
        theme="light"
      />
    </div>
  </Card>

  <Card>
    <div
      class="prose prose-lg max-w-none xl:prose-xl"
      v-html="page_outro"
    />
  </Card>
</template>
