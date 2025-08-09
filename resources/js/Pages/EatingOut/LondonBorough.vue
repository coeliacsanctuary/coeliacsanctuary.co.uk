<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Warning from '@/Components/Warning.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import { LondonBoroughPage } from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { ref } from 'vue';

defineProps<{ borough: LondonBoroughPage }>();

const areaList = ref<HTMLElement | null>(null);
</script>

<template>
  <TownHeading
    :county="borough.county"
    :image="borough.image"
    :name="borough.name"
    :latlng="borough.latlng"
    london-borough
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <div
      class="prose-md prose max-w-none lg:!my-0 lg:prose-lg *:first:lg:mt-0"
      v-html="borough.intro_text"
    />

    <p class="prose-md prose max-w-none lg:prose-lg">
      The wealth of information in our guide is a result of the generous
      contributions from people like you - fellow Coeliacs or individuals with
      gluten intolerance, who are familiar with their local area. These
      kind-hearted individuals take the time to share their knowledge and help
      us build a comprehensive list of places to eat to help others, like you!
    </p>

    <Warning>
      <p>
        While we take every care to make sure our eating out guide is accurate,
        places can change without notice, we always recommend that you check
        ahead before making plans.
      </p>

      <p class="mt-2">
        All eateries are recommended by our website visitors, and before going
        live we check menus and reviews, but we do not vet or visit places to
        independently check them.
      </p>
    </Warning>
  </Card>

  <GoogleAd
    :key="$page.url"
    code="5284484376"
  />

  <div
    ref="areaList"
    class="group grid gap-3 md:grid-cols-3"
  >
    <CountyTown
      v-for="area in borough.areas"
      :key="area.name"
      :town="area"
    />
  </div>

  <JumpToContentButton
    v-if="areaList"
    :anchor="areaList"
    label="Jump to Area List"
  />
</template>
