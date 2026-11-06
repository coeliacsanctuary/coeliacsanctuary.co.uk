<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { needsFullPageLoad } from '@/helpers';
import Icon from '@/Components/Icon.vue';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import Heading from '@/Components/Heading.vue';

type LinkType = {
  title: string;
  href: string;
  description: string;
  icon: string;
};

const links: LinkType[] = [
  {
    title: 'Eating Out Interactive Map',
    href: '/wheretoeat/browse',
    description:
      'Not sure where to go? Check out our interactive map showing all of the gluten places to eat across the UK and Ireland.',
    icon: 'map',
  },
  {
    title: 'Eating Out Guide',
    href: '/wheretoeat',
    description:
      "Coeliac Sanctuary's Eating Out guide, listing places all over the UK who can cater to Coeliac and gluten free diets, including attractions and hotels.",
    icon: 'eatery',
  },
  {
    title: 'Gluten Free Nationwide Chains',
    href: '/wheretoeat/nationwide',
    description:
      'Nationwide chains across the UK that can cater to gluten free diets',
    icon: 'dinner',
  },
  {
    title: 'Coeliac Sanctuary - On the Go App',
    href: '/wheretoeat/coeliac-sanctuary-on-the-go',
    description: 'Get all of our Gluten Free places to eat in your pocket!',
    icon: 'app',
  },
  {
    title: 'Recommend A Place',
    href: '/wheretoeat/recommend-a-place',
    description:
      'Do you know of somewhere missing from our eating out guide? Let us know!',
    icon: 'reviews',
  },
];

const elemClasses = (index: number): string[] => {
  const base: string[] = [
    'col-span-1',
    'flex',
    'transform',
    'cursor-pointer',
    'flex-col',
    'divide-y',
    'divide-gray-200',
    'rounded-lg',
    'bg-primary-light/50',
    'p-4',
    'text-center',
    'shadow-sm',
    'transition-all',
    'transform',
    'hover:scale-105',
    'hover:bg-primary-light/70',
    'hover:shadow-lg',
    'md:col-span-2',
  ];

  if (index === 3) {
    base.push('md:col-span-2', 'md:col-start-2', 'md:w-full');
  }

  if (index === 4) {
    base.push(
      'xs:col-span-2',
      'xs:mx-auto',
      'xs:w-1/2',
      'md:col-span-2',
      'md:w-full',
    );
  }

  return base;
};
</script>

<template>
  <Card>
    <Heading :border="false"> Gluten Free Places to Eat and Visit </Heading>
  </Card>

  <LocationSearch />

  <Card class="mt-3 flex flex-col space-y-4 md:p-6">
    <ul
      role="list"
      class="grid grid-cols-1 gap-4 xs:max-md:grid-cols-2 md:grid-cols-6 md:gap-6"
    >
      <li
        v-for="(item, index) in links"
        :key="item.title"
        :class="elemClasses(index)"
      >
        <component
          :is="needsFullPageLoad(item.href) ? 'a' : Link"
          :href="item.href"
          v-bind="needsFullPageLoad(item.href) ? {} : { prefetch: true }"
        >
          <div class="flex flex-1 flex-col space-y-4">
            <Icon
              class="mx-auto h-32 w-32 text-secondary"
              :name="item.icon"
            />

            <h3
              class="text-lg font-semibold md:max-lg:text-xl lg:text-2xl"
              v-text="item.title"
            />

            <p
              class="prose prose-sm max-w-none text-gray-500 md:max-lg:text-base lg:text-lg"
              v-text="item.description"
            />
          </div>
        </component>
      </li>
    </ul>
  </Card>
</template>
