<script lang="ts" setup>
import { DocumentArrowUpIcon, MapIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { County } from '@/types/EateryTypes';
import { computed } from 'vue';

const props = defineProps<{
  name: string;
  county: County;
  latlng: string;
  image?: string;
  londonBorough?: boolean;
}>();

const title = computed(() => {
  if (props.londonBorough) {
    return `the London borough of ${props.name}`;
  }

  return `${props.name}, ${props.county.name}`;
});

const linkCards = [
  {
    title: 'Recommend a place',
    description: `Do you know somewhere in <span class="font-semibold">${title.value}</span> that offers gluten free that we don't have listed? Let us know!`,
    icon: DocumentArrowUpIcon,
    href: '/wheretoeat/recommend-a-place',
  },
  {
    title: 'Map',
    description: `Browse an interactive map of <span class="font-semibold">${title.value}</span> with all of the places we know about marked that offer gluten free!`,
    icon: MapIcon,
    href: `/wheretoeat/browse/${props.latlng}/13`,
  },
];
</script>

<template>
  <div class="relative">
    <div
      class="my-3 xxs:absolute xxs:top-4 xxs:m-0 xxs:flex xxs:w-full xxs:justify-between xxs:p-3"
    >
      <div
        class="w-full bg-white p-2 text-center shadow-sm xxs:w-auto xxs:rounded-sm xxs:bg-primary-light/90 xxs:text-left xxs:shadow-lg xs:p-4"
      >
        <h1
          class="text-xl font-semibold xxs:max-sm:text-lg sm:max-md:text-xl md:max-lg:text-2xl lg:max-xl:text-3xl xl:text-4xl"
        >
          Gluten Free places to eat in {{ title }}
        </h1>

        <span
          class="mt-2 block text-sm font-semibold text-primary-dark hover:text-black md:text-base"
        >
          <Link :href="county.link">Back to {{ county.name }}</Link>
        </span>
      </div>
    </div>

    <div class="absolute bottom-0 mb-2 grid w-full grid-cols-2 gap-2 px-2">
      <Link
        v-for="item in linkCards"
        :key="item.title"
        class="shrink-0"
        :href="item.href"
        prefetch
      >
        <div
          class="flex h-full w-full cursor-pointer flex-col items-center justify-center space-y-2 rounded-sm bg-linear-to-br from-primary/90 to-primary-light/90 p-2 shadow-lg transition duration-500 sm:hover:from-primary/95 sm:hover:to-primary-light/95 md:justify-between md:p-4"
        >
          <div
            class="flex flex-1 flex-col items-center justify-center xs:w-full xs:flex-row md:flex-none md:items-start"
          >
            <div
              class="mb-2 hidden flex-1 xs:mr-4 xs:mb-0 xs:block xs:flex-none"
            >
              <component
                :is="item.icon"
                class="h-12 w-12 xs:max-md:h-10 xs:max-md:w-10 md:h-8 md:w-8"
              />
            </div>

            <div
              class="text-center text-sm font-semibold xs:flex-1 xs:text-left xs:max-md:text-base md:max-lg:text-xl lg:text-2xl"
            >
              {{ item.title }}
            </div>
          </div>
          <p
            class="hidden text-sm md:inline lg:text-base"
            v-html="item.description"
          />
        </div>
      </Link>
    </div>

    <img
      v-if="image"
      :alt="`Gluten Free places to eat in ${name}`"
      :src="image"
      class="mt-4 w-full shadow-sm"
      loading="lazy"
    />
  </div>
</template>
