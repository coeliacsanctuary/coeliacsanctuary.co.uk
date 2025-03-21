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
  <Card class="w-full max-w-[700px] mx-auto mt-5 rounded-xl">
    <main class="mx-auto w-full">
      <div class="text-center pt-4">
        <Heading :border="false">
          {{ title }}
        </Heading>

        <p
          class="mt-6 prose prose-xl"
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
            class="relative flex gap-x-6 py-6 group"
          >
            <div class="flex size-12 flex-none items-center justify-center">
              <component
                :is="link.icon"
                class="size-12 text-primary-dark group-hover:text-black transition transition-duration-500"
                aria-hidden="true"
              />
            </div>
            <div class="flex-auto">
              <SubHeading
                class="text-primary-dark group-hover:text-black transition transition-duration-500"
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
            class="prose font-semibold text-primary-dark hover:text-black transition"
          >
            Back to Coeliac Sanctuary
          </Link>
        </div>
      </div>
    </main>
  </Card>
</template>
