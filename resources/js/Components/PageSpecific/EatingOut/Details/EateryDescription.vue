<script lang="ts" setup>
import { DetailedEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Icon from '@/Components/Icon.vue';
import { computed } from 'vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const eateryName = computed(() => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return props.eatery.branch.name;
  }

  return props.eatery.name;
});
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <template v-if="eatery.restaurants.length">
      <SubHeading>
        Here's some restaurants in {{ eatery.name }} that have gluten free
        options
      </SubHeading>

      <div
        v-for="restaurant in eatery.restaurants"
        :key="restaurant.name"
        class="mt-4"
      >
        <h4
          v-if="restaurant.name"
          class="text-lg font-semibold md:text-xl"
        >
          {{ restaurant.name }}
        </h4>

        <p class="prose max-w-none md:prose-lg lg:prose-xl">
          {{ restaurant.info }}
        </p>
      </div>
    </template>

    <template v-else>
      <SubHeading> Here's what we know about {{ eateryName }} </SubHeading>

      <p
        class="prose mt-4 max-w-none sm:prose-lg lg:prose-xl"
        v-html="eatery.info"
      />
    </template>

    <ul
      v-if="eatery.features"
      class="grid grid-cols-1 gap-2 xxs:max-sm:grid-cols-2 xmd:gap-3 xmd:max-xl:grid-cols-4 sm:max-xmd:grid-cols-3 lg:gap-3 xl:grid-cols-6"
    >
      <li
        v-for="feature in eatery.features"
        :key="feature.slug"
        class="flex items-center space-x-2 leading-none lg:space-x-4"
      >
        <div class="h-8 w-8 shrink-0 text-primary lg:h-12 lg:w-12">
          <Icon
            :name="feature.slug"
            class="h-8 w-8 lg:h-12 lg:w-12"
          />
        </div>

        <span class="block leading-none font-semibold lg:text-xl">
          {{ feature.name }}
        </span>
      </li>
    </ul>

    <p class="text-sm italic">Last Updated: {{ eatery.last_updated_human }}</p>
  </Card>
</template>
