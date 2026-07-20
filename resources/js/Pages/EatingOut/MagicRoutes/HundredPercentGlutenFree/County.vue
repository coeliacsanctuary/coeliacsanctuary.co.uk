<script setup lang="ts">
import Card from '@/Components/Card.vue';
import BasicCountyHeading from '@/Components/PageSpecific/EatingOut/MagicRoutes/BasicCountyHeading.vue';
import {
  MagicRouteCountyPage,
  MagicRouteCountyTown,
} from '@/types/EateryTypes';
import Heading from '@/Components/Heading.vue';
import StarRating from '@/Components/StarRating.vue';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';

defineProps<{
  county: MagicRouteCountyPage;
  page_intro: string;
  towns: MagicRouteCountyTown[];
}>();
</script>

<template>
  <BasicCountyHeading
    :title="`Eating 100% Gluten Free in ${county.name}`"
    :county-name="county.name"
    :latlng="county.latlng"
    :back-link="county.link"
    :back-text="`Back to ${county.name}`"
    :image="county.image"
    additional-map-url="?features=100-gluten-free"
  />

  <Card>
    <div
      class="prose prose-lg max-w-none xl:prose-xl"
      v-html="page_intro"
    />
  </Card>

  <Card
    class="flex flex-col space-y-4"
    v-for="town in towns"
    :key="town.name"
  >
    <Heading classes="text-left">{{ town.name }}</Heading>

    <div
      class="prose prose-lg mt-0 max-w-none xl:prose-xl"
      v-html="town.intro"
    />

    <div
      v-for="eatery in town.eateries"
      class="mb-8 flex flex-col space-y-2 last:mb-4"
    >
      <div class="flex items-center justify-between">
        <h3
          class="text-2xl font-semibold text-primary-dark transition hover:text-black lg:text-3xl"
        >
          <Link :href="eatery.link">{{ eatery.name }}</Link>
        </h3>

        <StarRating
          v-if="eatery.reviews.number > 0"
          align="end"
          :rating="eatery.reviews.average"
          show-all
        />
      </div>

      <p
        class="prose prose-lg max-w-none xl:prose-xl"
        v-html="eatery.info"
      />

      <p
        class="prose prose-lg max-w-none font-semibold xl:prose-xl"
        v-text="eatery.location.address"
      />

      <div>
        <Link
          :href="eatery.link"
          class="text-xl font-semibold hover:underline xl:text-2xl"
        >
          Read more about {{ eatery.name }}
        </Link>
      </div>
    </div>

    <div>
      <CoeliacButton
        :href="town.link"
        :label="`View all places in ${town.name}`"
        size="lg"
        bold
        theme="light"
      />
    </div>
  </Card>
</template>
