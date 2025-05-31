<script setup lang="ts">
import { ChevronRightIcon } from '@heroicons/vue/20/solid';
import {
  CakeIcon,
  BookOpenIcon,
  CreditCardIcon,
  BuildingStorefrontIcon,
  ShoppingCartIcon,
} from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{
  title: string;
  description: string;
}>();

const links = [
  {
    name: 'Shop',
    href: '/shop',
    description: 'Find our travel cards, stickers and more!',
    icon: ShoppingCartIcon,
  },
  {
    name: 'Blogs',
    href: '/blog',
    description: 'Read about the latest goings on in the Coeliac world',
    icon: BookOpenIcon,
  },
  {
    name: 'Gluten Free Travel Cards',
    href: '/shop/standard-coeliac-travel-cards',
    description:
      'Help explain Coeliac and the need for gluten free, available in over 50 languages.',
    icon: CreditCardIcon,
  },
  {
    name: 'Recipes',
    href: '/recipe',
    description:
      'Need inspiration for your next gluten free project in the kitchen?',
    icon: CakeIcon,
  },
  {
    name: 'Eating Out',
    href: '/wheretoeat',
    description:
      'Looking for somewhere to eat in the UK? Check out our huge directory of places!',
    icon: BuildingStorefrontIcon,
  },
];
</script>

<template>
  <Card class="mx-auto mt-5 w-full max-w-[700px] rounded-xl">
    <main class="mx-auto w-full">
      <div class="pt-4 text-center">
        <Heading :border="false">
          {{ title }}
        </Heading>

        <p
          class="prose prose-xl mt-6"
          v-text="description"
        />
      </div>

      <div class="mx-auto mt-16 flow-root max-w-lg sm:mt-20">
        <ul
          role="list"
          class="-mt-6 divide-y divide-gray-900/5 border-b border-gray-900/5"
        >
          <li
            v-for="(link, linkIdx) in links"
            :key="linkIdx"
            class="group relative flex gap-x-6 py-6"
          >
            <div class="flex size-12 flex-none items-center justify-center">
              <component
                :is="link.icon"
                class="transition-duration-500 size-12 text-primary-dark transition group-hover:text-black"
                aria-hidden="true"
              />
            </div>
            <div class="flex-auto">
              <SubHeading
                class="transition-duration-500 text-primary-dark transition group-hover:text-black"
              >
                <Link :href="link.href">
                  {{ link.name }}
                </Link>
              </SubHeading>
              <p
                class="prose prose-lg"
                v-text="link.description"
              />
            </div>
            <div class="flex-none self-center">
              <ChevronRightIcon
                class="size-5 text-gray-400"
                aria-hidden="true"
              />
            </div>
          </li>
        </ul>
        <div class="mt-10 flex justify-center">
          <Link
            href="/"
            class="prose font-semibold text-primary-dark transition hover:text-black"
          >
            Back to Coeliac Sanctuary
          </Link>
        </div>
      </div>
    </main>
  </Card>
</template>
